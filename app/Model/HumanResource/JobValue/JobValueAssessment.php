<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\MasterModel;
use App\Model\Master\User;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeAreaValue;
use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Support\Facades\DB;
use App\Model\HumanResource\JobValue\JobValueAssessment;
use Carbon\Carbon;

class JobValueAssessment extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_assessments';

    protected $casts = [
        'total_value' => 'double',
        'total_score' => 'double',
        'score_diff' => 'double',
        'score_diff_pct' => 'double',
        'prev_area_value' => 'double',
        'area_value' => 'double',
        'area_value_diff' => 'double',
        'area_value_pct' => 'double',
        'kpi_avg' => 'double',
        'current_point' => 'double',
        'next_year_point' => 'double',
        'basic_fee' => 'double',
        'prev_fee' => 'double',
        'fee_add_pct' => 'double',
        'additional_fee' => 'double',
        'net_fee' => 'double',
        'jv_up_high' => 'double',
        'jv_up_normal' => 'double',
        'jv_static_high' => 'double',
        'jv_static_normal' => 'double'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'period_from', 'period_to', 'total_value', 'total_score'
    ];

    public function scores()
    {
        return $this->hasMany(JobValueAssessmentScore::class);
    }

    /**
     * Get the employee that owns the assessment.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function requestApprover()
    {
        return $this->belongsTo(User::class, 'request_approval_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function prevAssessment()
    {
        return $this->belongsTo(JobValueAssessment::class, 'prev_assessment_id');
    }

    public function areaValue()
    {
        return $this->belongsTo(EmployeeAreaValue::class, 'area_value_id');
    }

    public function prevAreaValue()
    {
        return $this->belongsTo(EmployeeAreaValue::class, 'prev_area_value_id');
    }

    public static function calculateFee($assessment){
        $assessment = $assessment->load('employee');

        // "Previous" = this employee's most recent approved assessment created
        // before the current one. Ordering by period_from alone breaks when two
        // assessments share the same period_from (e.g. re-assessing the same
        // period): a strict `period_from <` would skip the newer one and pick an
        // older period instead. So we also exclude the current row by id and
        // tie-break on id to always land on the latest prior assessment.
        $previousAssessment = JobValueAssessment::where('employee_id', $assessment->employee_id)
            ->when($assessment->id, function ($query) use ($assessment) {
                $query->where('id', '<', $assessment->id);
            })
            ->where('period_from', '<=', $assessment->period_from)
            ->where('approval_status', 'approved')
            ->orderBy('period_from', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($previousAssessment) {
            $assessment->prev_assessment_id = $previousAssessment->id;
            $assessment->score_diff = $assessment->total_value - $previousAssessment->total_value;
            $assessment->score_diff_pct = ($assessment->score_diff / $previousAssessment->total_value) * 100;
            $assessment->prev_fee = $previousAssessment->basic_fee;
        } else {
            $assessment->prev_assessment_id = null;
            $assessment->score_diff = 0;
            $assessment->score_diff_pct = 0;
            $assessment->prev_fee = 0;
        }


        $date = Carbon::parse($assessment->period_from);
        $currentYear = $date->format('Y');
        $areaValue = EmployeeAreaValue::where('job_location_id', $assessment->employee->employee_job_location_id)
            ->where('year', $currentYear)
            ->first();

        if ($areaValue) {
            $assessment->area_value_id = $areaValue->id;
            $assessment->area_value = $areaValue->value;
        } else {
            $assessment->area_value_id = null;
            $assessment->area_value = 0;
        }

        $prevAreaValue = EmployeeAreaValue::where('job_location_id', $assessment->employee->employee_job_location_id)
            ->where('year', $currentYear-1)
            ->first();


        if ($prevAreaValue) {
            $assessment->prev_area_value_id = $prevAreaValue->id;
            $assessment->prev_area_value = $prevAreaValue->value;
        } else {
            $assessment->prev_area_value_id = null;
            $assessment->prev_area_value = 0;
        }

        if ($assessment->prev_area_value > 0) {
            $assessment->area_value_diff = $assessment->area_value - $assessment->prev_area_value;
            $assessment->area_value_pct = ($assessment->area_value_diff / $assessment->prev_area_value) * 100;
        } else {
            $assessment->area_value_diff = 0;
            $assessment->area_value_pct = 0;
        }

        $jv = 376.19;

        $assessment->current_point = $assessment->prev_area_value / $jv;
        $assessment->next_year_point = $assessment->area_value / $jv;

        if ($assessment->score_diff_pct < $assessment->area_value_pct) {
            $assessment->basic_fee = $assessment->total_value * $assessment->current_point;
        } else {
            $assessment->basic_fee = $assessment->total_value * $assessment->next_year_point;
        }        

        $dateCreate = Carbon::parse($assessment->created_at);
        $currentYearCreate = $date->format('Y');
        $kpi = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'))
            ->where('status', 'COMPLETED')
            ->whereYear('kpis.date', '=', $currentYearCreate)
            ->where('employee_id', $assessment->employee_id)->orderBy('kpis.date', 'desc')->orderBy('kpis.created_at', 'desc')
            ->groupBy(DB::raw('year(kpis.date)'))
            ->first();

        $master = JobValueScoreSetting::first();

        if ($kpi) {
            $assessment->kpi_avg = $kpi->score_percentage;

            if (($assessment->kpi_avg - $master->minimum_kpi) > 0) {
                $assessment->fee_add_pct = $assessment->kpi_avg - $master->minimum_kpi;
            }
        }

        if ($previousAssessment) {
            $assessment->additional_fee = ($assessment->fee_add_pct / 100) * $previousAssessment->net_fee;
        } else {
            $assessment->additional_fee = 0;
        }

        $assessment->net_fee = $assessment->basic_fee + $assessment->additional_fee;

        $basicFee = $assessment->total_value * $assessment->current_point;

        $basicHigh = $assessment->total_value * $assessment->next_year_point;

        $assessment->jv_up_high = $basicHigh + $assessment->additional_fee;

        $assessment->jv_up_normal = $basicHigh;

        $assessment->jv_static_high = $basicFee + $assessment->additional_fee;

        $assessment->jv_static_normal = $basicFee;

        $assessment->save();

        return $assessment;
    }    
}