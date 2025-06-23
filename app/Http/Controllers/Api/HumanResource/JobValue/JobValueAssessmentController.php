<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\JobValue\JobValueAssessment\StoreJobValueAssessmentRequest;
use App\Http\Requests\HumanResource\JobValue\JobValueAssessment\UpdateJobValueAssessmentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\JobValue\JobValueAssessment;
use App\Model\HumanResource\JobValue\JobValueAssessmentCalculation;
use App\Model\HumanResource\JobValue\JobValueAssessmentScore;
use App\Model\HumanResource\JobValue\JobValueCategory;
use App\Model\HumanResource\JobValue\JobValueCriteria;
use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class JobValueAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function index(Request $request)
    {
        $permissions = tenant($request->user()->id)->getPermissions();
        $employee = Employee::where('user_id', $request->user()->id)->first();

        $assessment = JobValueAssessment::eloquentFilter($request)
            ->select('job_value_assessments.*');

        if (!tenant($request->user()->id)->hasPermissionTo('approve employee job value assessment', 'api')) {
            if ($employee) {
                $assessment->where('employee_id', $employee->id);
            } else {
                $assessment->where('employee_id', null);
            }
        }

        $assessment = pagination($assessment, $request->get('limit'));

        return new ApiCollection($assessment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\JobValueAssessment\StoreJobValueAssessmentRequest $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function store(StoreJobValueAssessmentRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $assessment = new JobValueAssessment();
            $assessment->employee_id = $request->input('employee_id');
            $assessment->period_from = $request->input('period_from');
            $assessment->period_to = $request->input('period_to');
            $assessment->status = $request->input('status');
            $assessment->approval_status = 'pending';
            $assessment->request_approval_to = $request->input('request_approval_to');
            $assessment->total_value = $request->input('total_value');
            $assessment->total_score = $request->input('total_score');
            $assessment->save();

            if ($request->has('scores')) {
                foreach ($request->scores as $scores) {
                    if ($scores['score'] > 0) {
                        try {
                            $assessment->scores()->create($scores);
                        } catch (\Exception $e) {
                            \Log::error('Error saving scale:', ['error' => $e->getMessage(), 'scores' => $scores]);
                        }
                    }                    
                }
            }

            $assessment = JobValueAssessment::calculateFee($assessment);

            return new ApiResource($assessment->load('scores'));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function show(Request $request, $id)
    {
        $assessment = JobValueAssessment::with([
            'scores' => function($query) {
                $query->select('job_value_assessment_scores.*')
                    ->join('job_value_criterias as criteria', 'criteria.id', '=', 'job_value_assessment_scores.criteria_id')
                    ->join('job_value_categories as category', 'category.id', '=', 'criteria.category_id')
                    ->orderBy('category.id');
            },
            'scores.criteria.category',
            'scores.criteria',
            'employee',
            'employee.scorers',
            'requestApprover',
            'prevAssessment',
            'approvedBy'
        ])
        ->where('id', $id)
        ->first();

        return new ApiResource($assessment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\JobValueAssessment\UpdateJobValueAssessmentRequest $request
     * @param  int $id
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function update(UpdateJobValueAssessmentRequest $request, $id)
    {
        $assessment = JobValueAssessment::findOrFail($id);
        $assessment->employee_id = $request->input('employee_id');
        $assessment->period_from = $request->input('period_from');
        $assessment->period_to = $request->input('period_to');
        $assessment->status = $request->input('status');
        $assessment->approval_status = 'pending';
        $assessment->request_approval_to = $request->input('request_approval_to');
        $assessment->total_value = $request->input('total_value');
        $assessment->total_score = $request->input('total_score');
        $assessment->save();

        foreach ($request->input('scores') as $score) {
            if (isset($score['id'])) {
                // Update existing area value
                $scoreValue = JobValueAssessmentScore::find($score['id']);
                if ($scoreValue) {
                    $scoreValue->update([
                        'criteria_id' => $score['criteria_id'],
                        'score' => $score['score'],
                        'description' => $score['description'],
                        'value' => $score['value'],
                        'note' => $score['note'],
                    ]);
                }
            } else {
                if ($score['score'] > 0) {
                    $assessment->scores()->create([
                        'criteria_id' => $score['criteria_id'],
                        'score' => $score['score'],
                        'description' => $score['description'],
                        'value' => $score['value'],
                        'note' => $score['note'],
                    ]);
                }
            }
        }

        $assessment = JobValueAssessment::calculateFee($assessment);

        return new ApiResource($assessment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function destroy($id)
    {
        $assessment = JobValueAssessment::findOrFail($id);
        
        $nextAssessment = JobValueAssessment::where('prev_assessment_id', $id)->first();

        if ($nextAssessment) {
            return response()->json([
                'message' => 'Cannot delete because this assessment is used in the next assessment.'
            ], 400);
        }

        $assessment->delete();

        return response(null, 204);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function showCalculation(Request $request, $id)
    {
        $assessment = JobValueAssessmentCalculation::eloquentFilter($request)
            ->select('job_value_assessment_calculations.*')
            ->where('assessment_id', $id)
            ->first();

        return new ApiResource($assessment);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function getCocValue(Request $request)
    {
        $date = Carbon::parse($request->start_date);
        $currentYear = $date->format('Y');
        $kpi = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'))
            ->where('status', 'COMPLETED')
            ->whereIn(DB::raw('LOWER(kpi_groups.name)'), ['solusi', 'andalan', 'emas', 'besar', 'besar (1)', 'besar (2)', 'terus terang'])
            ->whereYear('kpis.date', '=', $currentYear)
            ->where('employee_id', $request->employee_id)->orderBy('kpis.date', 'desc')->orderBy('kpis.created_at', 'desc')
            ->groupBy('kpis.date')
            ->first();

        return new ApiResource($kpi);
    }
}