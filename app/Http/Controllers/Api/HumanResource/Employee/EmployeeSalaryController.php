<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\Employee\EmployeeSalary\StoreEmployeeSalaryRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeSalary\UpdateEmployeeSalaryRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryCollection;
use App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryResource;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Model\HumanResource\Employee\EmployeeSalaryAchievement;
use App\Model\HumanResource\Employee\EmployeeSalaryAdditionalComponent;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessment;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentScore;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentTarget;
use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $employeeId
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryCollection
     */
    public function index($employeeId)
    {
        $type = request()->get('type');

        $employee_salaries = EmployeeSalary::select('employee_salaries.*')
                                ->where('employee_salaries.employee_id', $employeeId);

        if ($type === 'all') {
            $employee_salaries = $employee_salaries->groupBy('employee_salaries.id');
        }
        if ($type === 'weekly') {
            $employee_salaries = $employee_salaries->groupBy(DB::raw('yearweek(employee_salaries.end_date)'));
        }
        if ($type === 'monthly') {
            $employee_salaries = $employee_salaries->groupBy(DB::raw('year(employee_salaries.end_date)'), DB::raw('month(employee_salaries.end_date)'));
        }

        $employee_salaries = $employee_salaries->where('employee_id', $employeeId)->orderBy('employee_salaries.end_date', 'desc');

        $employee_salaries = pagination($employee_salaries, 15);

        foreach ($employee_salaries as &$employee_salary) {
            $salaries_data = EmployeeSalary::select('employee_salaries.*');

            if ($type === 'all') {
                $salaries_data = $salaries_data->where(DB::raw('employee_salaries.id'), DB::raw("'$employee_salary->id'"));
            }
            if ($type === 'weekly') {
                $salaries_data = $salaries_data->where(DB::raw('yearweek(employee_salaries.end_date)'), DB::raw("yearweek('$employee_salary->end_date')"));
            }
            if ($type === 'monthly') {
                $salaries_data = $salaries_data->where(DB::raw('EXTRACT(YEAR_MONTH from employee_salaries.end_date)'), DB::raw("EXTRACT(YEAR_MONTH from '$employee_salary->end_date')"));
            }

            $salaries_data = $salaries_data->where('employee_salaries.employee_id', $employeeId);
            $salaries_data = $salaries_data->get();

            foreach ($salaries_data as $data) {
                $calculated = $data->getCalculationSalaryData($data->getAdditionalSalaryData($data->assessments, $data->achievements));
                $employee_salary->total_amount_receivable += $calculated['total_amount_received'];
            }
        }

        return new EmployeeSalaryCollection($employee_salaries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreEmployeeSalaryRequest $request
     * @param                           $employeeId
     * @return ApiResource|array
     */
    public function store(StoreEmployeeSalaryRequest $request, $employeeId)
    {
        if ($request->get('date') && count($request->get('date')) == 2) {
            $date = $request->get('date');

            $startDate = date('Y-m-d', strtotime($date['start']));
            $endDate = date('Y-m-d', strtotime($date['end']));

            $assessments = $request->get('salary_assessment');
            $achievements = $request->get('salary_achievement');

            if (count($assessments['indicators']) == 0) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Assessment is required',
                ], 422);
            }

            DB::connection('tenant')->beginTransaction();

            $employee_salary = new EmployeeSalary;
            $employee_salary->employee_id = $employeeId;
            $employee_salary->job_location = $request->get('job_location');
            $employee_salary->start_date = $startDate;
            $employee_salary->end_date = $endDate;
            $employee_salary->base_salary = $request->get('base_salary');
            $employee_salary->multiplier_kpi = $request->get('multiplier_kpi');
            $employee_salary->daily_transport_allowance = $request->get('daily_transport_allowance');
            $employee_salary->active_days_in_month = $request->get('active_days_in_month') ?? 0;

            if (EmployeeSalary::getWeekOfMonth($employee_salary->start_date) === 1) {
                $employee_salary->functional_allowance = $request->get('functional_allowance');
                $employee_salary->communication_allowance = $request->get('communication_allowance');
            } else {
                $employee_salary->functional_allowance = 0;
                $employee_salary->communication_allowance = 0;
            }

            $employee_salary->active_days_week1 = $request->get('active_days_week_1') ?? 0;
            $employee_salary->active_days_week2 = $request->get('active_days_week_2') ?? 0;
            $employee_salary->active_days_week3 = $request->get('active_days_week_3') ?? 0;
            $employee_salary->active_days_week4 = $request->get('active_days_week_4') ?? 0;
            $employee_salary->active_days_week5 = $request->get('active_days_week_5') ?? 0;

            $employee_salary->receivable_cut_60_days_week1 = $request->get('receivable_cut_60_days_week_1') ?? 0;
            $employee_salary->receivable_cut_60_days_week2 = $request->get('receivable_cut_60_days_week_2') ?? 0;
            $employee_salary->receivable_cut_60_days_week3 = $request->get('receivable_cut_60_days_week_3') ?? 0;
            $employee_salary->receivable_cut_60_days_week4 = $request->get('receivable_cut_60_days_week_4') ?? 0;
            $employee_salary->receivable_cut_60_days_week5 = $request->get('receivable_cut_60_days_week_5') ?? 0;

            $employee_salary->overdue_receivable_week1 = $request->get('overdue_receivable_week_1') ?? 0;
            $employee_salary->overdue_receivable_week2 = $request->get('overdue_receivable_week_2') ?? 0;
            $employee_salary->overdue_receivable_week3 = $request->get('overdue_receivable_week_3') ?? 0;
            $employee_salary->overdue_receivable_week4 = $request->get('overdue_receivable_week_4') ?? 0;
            $employee_salary->overdue_receivable_week5 = $request->get('overdue_receivable_week_5') ?? 0;

            $employee_salary->payment_from_marketing_week1 = $request->get('payment_from_marketing_week_1') ?? 0;
            $employee_salary->payment_from_marketing_week2 = $request->get('payment_from_marketing_week_2') ?? 0;
            $employee_salary->payment_from_marketing_week3 = $request->get('payment_from_marketing_week_3') ?? 0;
            $employee_salary->payment_from_marketing_week4 = $request->get('payment_from_marketing_week_4') ?? 0;
            $employee_salary->payment_from_marketing_week5 = $request->get('payment_from_marketing_week_5') ?? 0;

            $employee_salary->payment_from_sales_week1 = $request->get('payment_from_sales_week_1') ?? 0;
            $employee_salary->payment_from_sales_week2 = $request->get('payment_from_sales_week_2') ?? 0;
            $employee_salary->payment_from_sales_week3 = $request->get('payment_from_sales_week_3') ?? 0;
            $employee_salary->payment_from_sales_week4 = $request->get('payment_from_sales_week_4') ?? 0;
            $employee_salary->payment_from_sales_week5 = $request->get('payment_from_sales_week_5') ?? 0;

            $employee_salary->payment_from_spg_week1 = $request->get('payment_from_spg_week_1') ?? 0;
            $employee_salary->payment_from_spg_week2 = $request->get('payment_from_spg_week_2') ?? 0;
            $employee_salary->payment_from_spg_week3 = $request->get('payment_from_spg_week_3') ?? 0;
            $employee_salary->payment_from_spg_week4 = $request->get('payment_from_spg_week_4') ?? 0;
            $employee_salary->payment_from_spg_week5 = $request->get('payment_from_spg_week_5') ?? 0;

            $employee_salary->cash_payment_week1 = $request->get('cash_payment_week_1') ?? 0;
            $employee_salary->cash_payment_week2 = $request->get('cash_payment_week_2') ?? 0;
            $employee_salary->cash_payment_week3 = $request->get('cash_payment_week_3') ?? 0;
            $employee_salary->cash_payment_week4 = $request->get('cash_payment_week_4') ?? 0;
            $employee_salary->cash_payment_week5 = $request->get('cash_payment_week_5') ?? 0;

            $employee_salary->weekly_sales_week1 = $request->get('weekly_sales_week_1') ?? 0;
            $employee_salary->weekly_sales_week2 = $request->get('weekly_sales_week_2') ?? 0;
            $employee_salary->weekly_sales_week3 = $request->get('weekly_sales_week_3') ?? 0;
            $employee_salary->weekly_sales_week4 = $request->get('weekly_sales_week_4') ?? 0;
            $employee_salary->weekly_sales_week5 = $request->get('weekly_sales_week_5') ?? 0;

            $employee_salary->wa_daily_report_week1 = $request->get('wa_daily_report_week_1') ?? 0;
            $employee_salary->wa_daily_report_week2 = $request->get('wa_daily_report_week_2') ?? 0;
            $employee_salary->wa_daily_report_week3 = $request->get('wa_daily_report_week_3') ?? 0;
            $employee_salary->wa_daily_report_week4 = $request->get('wa_daily_report_week_4') ?? 0;
            $employee_salary->wa_daily_report_week5 = $request->get('wa_daily_report_week_5') ?? 0;

            $employee_salary->save();

            foreach ($assessments['indicators'] as $assessment) {
                $salaryAssessment = new EmployeeSalaryAssessment;
                $salaryAssessment->employee_salary_id = $employee_salary->id;
                $salaryAssessment->name = $assessment['name'];
                $salaryAssessment->weight = (float) $assessment['weight'];
                $salaryAssessment->save();

                foreach ($assessment['score'] as $key => $score) {
                    $assessmentScore = new EmployeeSalaryAssessmentScore;
                    $assessmentScore->assessment_id = $salaryAssessment->id;
                    $assessmentScore->week_of_month = $key;
                    $assessmentScore->score = $score;
                    $assessmentScore->save();
                }

                foreach ($assessment['target'] as $key => $target) {
                    $assessmentTarget = new EmployeeSalaryAssessmentTarget;
                    $assessmentTarget->assessment_id = $salaryAssessment->id;
                    $assessmentTarget->week_of_month = $key;
                    $assessmentTarget->target = $target;
                    $assessmentTarget->save();
                }
            }

            foreach ($achievements['automated'] as $key => $achievement) {
                $salaryAchievement = new EmployeeSalaryAchievement;
                $salaryAchievement->employee_salary_id = $employee_salary->id;
                $salaryAchievement->name = $achievement['name'];
                $salaryAchievement->weight = (float) $achievement['weight'];
                $salaryAchievement->week1 = array_key_exists('week1', $achievement) ? (float) $achievement['week1'] : 0;
                $salaryAchievement->week2 = array_key_exists('week2', $achievement) ? (float) $achievement['week2'] : 0;
                $salaryAchievement->week3 = array_key_exists('week3', $achievement) ? (float) $achievement['week3'] : 0;
                $salaryAchievement->week4 = array_key_exists('week4', $achievement) ? (float) $achievement['week4'] : 0;
                $salaryAchievement->week5 = array_key_exists('week5', $achievement) ? (float) $achievement['week5'] : 0;
                $salaryAchievement->save();
            }

            DB::connection('tenant')->commit();

            return new ApiResource($employee_salary);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $employee_id
     * @param  int $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryResource
     */
    public function show($employeeId, $id)
    {
        $employee_salary = EmployeeSalary::where('employee_salaries.employee_id', $employeeId)
            ->where('employee_salaries.id', $id)
            ->first();

        return (new EmployeeSalaryResource($employee_salary))
            ->additional([
                'additional' => $employee_salary->getAdditionalSalaryData($employee_salary->assessments, $employee_salary->achievements),
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param $employeeId
     * @param $group
     * @return KpiResource
     */
    public function showBy(Request $request, $employeeId, $group)
    {
        $type = $request->get('type');

        $template_salary = EmployeeSalary::where('employee_salaries.employee_id', $employeeId)
            ->where('employee_salaries.id', $group)
            ->first();

        $employee_salaries = EmployeeSalary::select('employee_salaries.*')
                                ->where('employee_salaries.employee_id', $employeeId)
                                ->groupBy('employee_salaries.id');

        if ($type === 'weekly') {
            $employee_salaries = $employee_salaries->where(DB::raw('yearweek(employee_salaries.end_date)'), DB::raw("yearweek('$template_salary->end_date')"));
        }
        if ($type === 'monthly') {
            $employee_salaries = $employee_salaries->where(DB::raw('EXTRACT(YEAR_MONTH from employee_salaries.end_date)'), DB::raw("EXTRACT(YEAR_MONTH from '$template_salary->end_date')"));
        }

        $employee_salaries = $employee_salaries->get();

        $response = [];
        $response['employee_id'] = $template_salary->employee_id;
        $response['start_date'] = $template_salary->start_date;
        $response['end_date'] = $template_salary->end_date;
        $response['job_location'] = $template_salary->job_location;
        $response['base_salary'] = $template_salary->base_salary;
        $response['multiplier_kpi'] = $template_salary->multiplier_kpi;
        $response['daily_transport_allowance'] = $template_salary->daily_transport_allowance;
        $response['functional_allowance'] = $template_salary->functional_allowance;
        $response['communication_allowance'] = $template_salary->communication_allowance;
        $response['active_days_in_month'] = $template_salary->active_days_in_month;

        $response['active_days_week1'] = $response['active_days_week2'] = $response['active_days_week3'] = $response['active_days_week4'] = $response['active_days_week5'] = 0;

        $response['receivable_cut_60_days_week1'] = $response['receivable_cut_60_days_week2'] = $response['receivable_cut_60_days_week3'] = $response['receivable_cut_60_days_week4'] = $response['receivable_cut_60_days_week5'] = 0;

        $response['overdue_receivable_week1'] = $response['overdue_receivable_week2'] = $response['overdue_receivable_week3'] = $response['overdue_receivable_week4'] = $response['overdue_receivable_week5'] = 0;

        $response['payment_from_marketing_week1'] = $response['payment_from_marketing_week2'] = $response['payment_from_marketing_week3'] = $response['payment_from_marketing_week4'] = $response['payment_from_marketing_week5'] = 0;

        $response['payment_from_sales_week1'] = $response['payment_from_sales_week2'] = $response['payment_from_sales_week3'] = $response['payment_from_sales_week4'] = $response['payment_from_sales_week5'] = 0;

        $response['payment_from_spg_week1'] = $response['payment_from_spg_week2'] = $response['payment_from_spg_week3'] = $response['payment_from_spg_week4'] = $response['payment_from_spg_week5'] = 0;

        $response['cash_payment_week1'] = $response['cash_payment_week2'] = $response['cash_payment_week3'] = $response['cash_payment_week4'] = $response['cash_payment_week5'] = 0;

        $response['weekly_sales_week1'] = $response['weekly_sales_week2'] = $response['weekly_sales_week3'] = $response['weekly_sales_week4'] = $response['weekly_sales_week5'] = 0;

        $response['wa_daily_report_week1'] = $response['wa_daily_report_week2'] = $response['wa_daily_report_week3'] = $response['wa_daily_report_week4'] = $response['wa_daily_report_week5'] = 0;

        $response['assessments'] = [];
        $response['achievements'] = [];

        foreach ($template_salary->assessments as $key => $assessment) {
            $response['assessments'][$assessment->name] = [];
            $response['assessments'][$assessment->name]['id'] = $key;
            $response['assessments'][$assessment->name]['name'] = $assessment->name;
            $response['assessments'][$assessment->name]['scores'] = [];
            $response['assessments'][$assessment->name]['targets'] = [];
            $response['assessments'][$assessment->name]['weight'] = $assessment->weight;
        }

        foreach ($template_salary->achievements as $key => $achievement) {
            $response['achievements'][$achievement->name] = [];
            $response['achievements'][$achievement->name]['id'] = $key;
            $response['achievements'][$achievement->name]['name'] = $achievement->name;
            $response['achievements'][$achievement->name]['week1'] = 0;
            $response['achievements'][$achievement->name]['week2'] = 0;
            $response['achievements'][$achievement->name]['week3'] = 0;
            $response['achievements'][$achievement->name]['week4'] = 0;
            $response['achievements'][$achievement->name]['week5'] = 0;
            $response['achievements'][$achievement->name]['weight'] = $achievement->weight;
        }

        $additional = [];

        foreach ($employee_salaries as $salary) {
            if ($type === 'weekly') {
                if ($salary->start_date < $template_salary->start_date) {
                    $response['start_date'] = $salary->start_date;
                }
                if ($salary->end_date > $template_salary->end_date) {
                    $response['end_date'] = $salary->end_date;
                }
            }
            if ($type === 'monthly') {
                $response['start_date'] = date('Y-m-01', strtotime($salary->start_date));
                $response['end_date'] = date('Y-m-t', strtotime($salary->end_date));
            }

            $response['active_days_week1'] += $salary->active_days_week1;
            $response['active_days_week2'] += $salary->active_days_week2;
            $response['active_days_week3'] += $salary->active_days_week3;
            $response['active_days_week4'] += $salary->active_days_week4;
            $response['active_days_week5'] += $salary->active_days_week5;

            $response['receivable_cut_60_days_week1'] += $salary->receivable_cut_60_days_week1;
            $response['receivable_cut_60_days_week2'] += $salary->receivable_cut_60_days_week2;
            $response['receivable_cut_60_days_week3'] += $salary->receivable_cut_60_days_week3;
            $response['receivable_cut_60_days_week4'] += $salary->receivable_cut_60_days_week4;
            $response['receivable_cut_60_days_week5'] += $salary->receivable_cut_60_days_week5;

            $response['overdue_receivable_week1'] += $salary->overdue_receivable_week1;
            $response['overdue_receivable_week2'] += $salary->overdue_receivable_week2;
            $response['overdue_receivable_week3'] += $salary->overdue_receivable_week3;
            $response['overdue_receivable_week4'] += $salary->overdue_receivable_week4;
            $response['overdue_receivable_week5'] += $salary->overdue_receivable_week5;

            $response['payment_from_marketing_week1'] += $salary->payment_from_marketing_week1;
            $response['payment_from_marketing_week2'] += $salary->payment_from_marketing_week2;
            $response['payment_from_marketing_week3'] += $salary->payment_from_marketing_week3;
            $response['payment_from_marketing_week4'] += $salary->payment_from_marketing_week4;
            $response['payment_from_marketing_week5'] += $salary->payment_from_marketing_week5;

            $response['payment_from_sales_week1'] += $salary->payment_from_sales_week1;
            $response['payment_from_sales_week2'] += $salary->payment_from_sales_week2;
            $response['payment_from_sales_week3'] += $salary->payment_from_sales_week3;
            $response['payment_from_sales_week4'] += $salary->payment_from_sales_week4;
            $response['payment_from_sales_week5'] += $salary->payment_from_sales_week5;

            $response['payment_from_spg_week1'] += $salary->payment_from_spg_week1;
            $response['payment_from_spg_week2'] += $salary->payment_from_spg_week2;
            $response['payment_from_spg_week3'] += $salary->payment_from_spg_week3;
            $response['payment_from_spg_week4'] += $salary->payment_from_spg_week4;
            $response['payment_from_spg_week5'] += $salary->payment_from_spg_week5;

            $response['cash_payment_week1'] += $salary->cash_payment_week1;
            $response['cash_payment_week2'] += $salary->cash_payment_week2;
            $response['cash_payment_week3'] += $salary->cash_payment_week3;
            $response['cash_payment_week4'] += $salary->cash_payment_week4;
            $response['cash_payment_week5'] += $salary->cash_payment_week5;

            $response['weekly_sales_week1'] += $salary->weekly_sales_week1;
            $response['weekly_sales_week2'] += $salary->weekly_sales_week2;
            $response['weekly_sales_week3'] += $salary->weekly_sales_week3;
            $response['weekly_sales_week4'] += $salary->weekly_sales_week4;
            $response['weekly_sales_week5'] += $salary->weekly_sales_week5;

            $response['wa_daily_report_week1'] += $salary->wa_daily_report_week1;
            $response['wa_daily_report_week2'] += $salary->wa_daily_report_week2;
            $response['wa_daily_report_week3'] += $salary->wa_daily_report_week3;
            $response['wa_daily_report_week4'] += $salary->wa_daily_report_week4;
            $response['wa_daily_report_week5'] += $salary->wa_daily_report_week5;

            foreach ($salary->assessments as $assessment) {
                $key = $assessment->name;
                if (array_key_exists($key, $response['assessments'])) {
                    foreach ($assessment->scores as $score) {
                        $score = [
                            'score' => $score->score,
                            'week_of_month' => $score->week_of_month,
                        ];
                        array_push($response['assessments'][$key]['scores'], $score);
                    }

                    foreach ($assessment->targets as $target) {
                        $target = [
                            'target' => $target->target,
                            'week_of_month' => $target->week_of_month,
                        ];
                        array_push($response['assessments'][$key]['targets'], $target);
                    }
                }
            }

            foreach ($salary->achievements as $achievement) {
                $key = $achievement->name;
                if (array_key_exists($key, $response['achievements'])) {
                    if ($key === 'balance') {
                        $response['achievements'][$key]['week1'] = $achievement->week1;
                        $response['achievements'][$key]['week2'] = $achievement->week2;
                        $response['achievements'][$key]['week3'] = $achievement->week3;
                        $response['achievements'][$key]['week4'] = $achievement->week4;
                        $response['achievements'][$key]['week5'] = $achievement->week5;
                    } else {
                        $response['achievements'][$key]['week1'] += $achievement->week1;
                        $response['achievements'][$key]['week2'] += $achievement->week2;
                        $response['achievements'][$key]['week3'] += $achievement->week3;
                        $response['achievements'][$key]['week4'] += $achievement->week4;
                        $response['achievements'][$key]['week5'] += $achievement->week5;
                    }
                }
            }
        }

        $response['assessments'] = array_values($response['assessments']);
        $response['achievements'] = array_values($response['achievements']);
        $response['additional'] = $template_salary->getAdditionalSalaryDataShowBy($response['assessments'], $response['achievements']);

        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\Employee\UpdateEmployeeSalaryRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeeSalaryRequest $request, $employeeId, $id)
    {
        $salary = $request->get('salary');

        DB::connection('tenant')->beginTransaction();

        $employee_salary = EmployeeSalary::findOrFail($id);

        $employee_salary->active_days_in_month = $salary['active_days_in_month'] ?? 0;

        $employee_salary->active_days_week1 = $salary['active_days_week1'] ?? 0;
        $employee_salary->active_days_week2 = $salary['active_days_week2'] ?? 0;
        $employee_salary->active_days_week3 = $salary['active_days_week3'] ?? 0;
        $employee_salary->active_days_week4 = $salary['active_days_week4'] ?? 0;
        $employee_salary->active_days_week5 = $salary['active_days_week5'] ?? 0;

        $employee_salary->receivable_cut_60_days_week1 = $salary['receivable_cut_60_days_week1'] ?? 0;
        $employee_salary->receivable_cut_60_days_week2 = $salary['receivable_cut_60_days_week2'] ?? 0;
        $employee_salary->receivable_cut_60_days_week3 = $salary['receivable_cut_60_days_week3'] ?? 0;
        $employee_salary->receivable_cut_60_days_week4 = $salary['receivable_cut_60_days_week4'] ?? 0;
        $employee_salary->receivable_cut_60_days_week5 = $salary['receivable_cut_60_days_week5'] ?? 0;

        $employee_salary->overdue_receivable_week1 = $salary['overdue_receivable_week1'] ?? 0;
        $employee_salary->overdue_receivable_week2 = $salary['overdue_receivable_week2'] ?? 0;
        $employee_salary->overdue_receivable_week3 = $salary['overdue_receivable_week3'] ?? 0;
        $employee_salary->overdue_receivable_week4 = $salary['overdue_receivable_week4'] ?? 0;
        $employee_salary->overdue_receivable_week5 = $salary['overdue_receivable_week5'] ?? 0;

        $employee_salary->payment_from_marketing_week1 = $salary['payment_from_marketing_week1'] ?? 0;
        $employee_salary->payment_from_marketing_week2 = $salary['payment_from_marketing_week2'] ?? 0;
        $employee_salary->payment_from_marketing_week3 = $salary['payment_from_marketing_week3'] ?? 0;
        $employee_salary->payment_from_marketing_week4 = $salary['payment_from_marketing_week4'] ?? 0;
        $employee_salary->payment_from_marketing_week5 = $salary['payment_from_marketing_week5'] ?? 0;

        $employee_salary->payment_from_sales_week1 = $salary['payment_from_sales_week1'] ?? 0;
        $employee_salary->payment_from_sales_week2 = $salary['payment_from_sales_week2'] ?? 0;
        $employee_salary->payment_from_sales_week3 = $salary['payment_from_sales_week3'] ?? 0;
        $employee_salary->payment_from_sales_week4 = $salary['payment_from_sales_week4'] ?? 0;
        $employee_salary->payment_from_sales_week5 = $salary['payment_from_sales_week5'] ?? 0;

        $employee_salary->payment_from_spg_week1 = $salary['payment_from_spg_week1'] ?? 0;
        $employee_salary->payment_from_spg_week2 = $salary['payment_from_spg_week2'] ?? 0;
        $employee_salary->payment_from_spg_week3 = $salary['payment_from_spg_week3'] ?? 0;
        $employee_salary->payment_from_spg_week4 = $salary['payment_from_spg_week4'] ?? 0;
        $employee_salary->payment_from_spg_week5 = $salary['payment_from_spg_week5'] ?? 0;

        $employee_salary->cash_payment_week1 = $salary['cash_payment_week1'] ?? 0;
        $employee_salary->cash_payment_week2 = $salary['cash_payment_week2'] ?? 0;
        $employee_salary->cash_payment_week3 = $salary['cash_payment_week3'] ?? 0;
        $employee_salary->cash_payment_week4 = $salary['cash_payment_week4'] ?? 0;
        $employee_salary->cash_payment_week5 = $salary['cash_payment_week5'] ?? 0;

        $employee_salary->weekly_sales_week1 = $salary['weekly_sales_week1'] ?? 0;
        $employee_salary->weekly_sales_week2 = $salary['weekly_sales_week2'] ?? 0;
        $employee_salary->weekly_sales_week3 = $salary['weekly_sales_week3'] ?? 0;
        $employee_salary->weekly_sales_week4 = $salary['weekly_sales_week4'] ?? 0;
        $employee_salary->weekly_sales_week5 = $salary['weekly_sales_week5'] ?? 0;

        $employee_salary->wa_daily_report_week1 = $salary['wa_daily_report_week1'] ?? 0;
        $employee_salary->wa_daily_report_week2 = $salary['wa_daily_report_week2'] ?? 0;
        $employee_salary->wa_daily_report_week3 = $salary['wa_daily_report_week3'] ?? 0;
        $employee_salary->wa_daily_report_week4 = $salary['wa_daily_report_week4'] ?? 0;
        $employee_salary->wa_daily_report_week5 = $salary['wa_daily_report_week5'] ?? 0;

        $employee_salary->save();

        foreach ($salary['assessments'] as $key => $assessment) {
            $salaryAssessment = EmployeeSalaryAssessment::findOrFail($assessment['id']);
            $salaryAssessment->weight = (float) $assessment['weight'];
            $salaryAssessment->save();
        }

        foreach ($salary['achievements'] as $key => $achievement) {
            $salaryAchievement = EmployeeSalaryAchievement::findOrFail($achievement['id']);
            $salaryAchievement->weight = (float) $achievement['weight'];
            $salaryAchievement->save();
        }

        DB::connection('tenant')->commit();

        return new ApiResource($employee_salary);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryResource
     */
    public function destroy($employeeId, $id)
    {
        $employee_salary = EmployeeSalary::findOrFail($id);

        $employee_salary->delete();

        return response(null, 204);
    }

    /**
     * Display a listing of the assessments.
     *
     * @param  \Illuminate\Http\Request $request
     * @param                           $employeeId
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function assessment(Request $request, $employeeId)
    {
        $dateFrom = convert_to_server_timezone(date('Y-m-d H:i:s', strtotime($request->get('startDate'))));
        $dateTo = convert_to_server_timezone(date('Y-m-d H:i:s', strtotime($request->get('endDate'))));

        $kpis = Kpi::select('kpis.*')
            ->addSelect(DB::raw('CONCAT("week", (FLOOR((DAYOFMONTH(kpis.date) - 1) / 7) + 1)) AS week_of_month'))
            ->addSelect(DB::raw('DATE(kpis.date + INTERVAL (0 - WEEKDAY(kpis.date)) DAY) as first_day_of_week'))
            ->addSelect(DB::raw('DATE(kpis.date + INTERVAL (5 - WEEKDAY(kpis.date)) DAY) as last_day_of_week'));

        $kpis = $kpis->whereBetween('kpis.date', [$dateFrom, $dateTo]);
        $kpis = $kpis->where('employee_id', $employeeId)->orderBy('kpis.date', 'asc')->get();

        $employee_assessment = [
            'indicators' => [],
            'total' => [
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0,
                'weight' => 0,
            ],
        ];

        $indicatorIndex = 0;

        foreach ($kpis as $key => $kpi) {
            foreach ($kpi->groups as $key => $group) {
                foreach ($group->indicators as $key => $indicator) {
                    $indicatorName = strtolower($indicator->name);
                    if (! array_key_exists($indicatorName, $employee_assessment['indicators'])) {
                        $indicator_data = [];
                        $indicator_data['id'] = ++$indicatorIndex;
                        $indicator_data['name'] = $indicator->name;
                        $indicator_data['weight'] = $indicator->weight;
                        $indicator_data['target'] = [];
                        $indicator_data['score'] = [];

                        if ($indicator->automated_code) {
                            $indicator_data['automated_code'] = $indicator->automated_code;
                        }

                        $employee_assessment['indicators'][$indicatorName] = $indicator_data;
                    }

                    if (array_key_exists($kpi->week_of_month, $employee_assessment['indicators'][$indicatorName]['target'])) {
                        $employee_assessment['indicators'][$indicatorName]['target'][$kpi->week_of_month] += $indicator->target;
                    } else {
                        $employee_assessment['indicators'][$indicatorName]['target'][$kpi->week_of_month] = $indicator->target;
                    }

                    if (array_key_exists($kpi->week_of_month, $employee_assessment['indicators'][$indicatorName]['score'])) {
                        $employee_assessment['indicators'][$indicatorName]['score'][$kpi->week_of_month] += $indicator->score;
                    } else {
                        $employee_assessment['indicators'][$indicatorName]['score'][$kpi->week_of_month] = $indicator->score;
                    }
                }
            }
        }

        foreach ($employee_assessment['indicators'] as $key => &$assessment) {
            foreach ($assessment['score'] as $week => $score) {
                $target = $employee_assessment['indicators'][$key]['target'][$week];
                $score = $employee_assessment['indicators'][$key]['score'][$week];

                $score_percentage = $target > 0 ? $score / $target * 100 : 0;

                if ($score_percentage > 100 && stripos($key, 'pelunasan piutang') === false) {
                    $score_percentage = 100;
                }

                $employee_assessment['indicators'][$key]['score_percentage'][$week] = $score_percentage;
                $employee_assessment['total'][$week] += (float) $score_percentage * $assessment['weight'] / 100;
            }

            $employee_assessment['total']['weight'] += (float) $assessment['weight'];
        }

        $employee_assessment['indicators'] = array_values($employee_assessment['indicators']);

        return ['data' => $employee_assessment];
    }

    /**
     * Display a listing of the achievements.
     *
     * @param  \Illuminate\Http\Request $request
     * @param                           $employeeId
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function achievement(Request $request, $employeeId)
    {
        $dateFrom = convert_to_server_timezone(date('Y-m-d H:i:s', strtotime($request->get('startDate'))));
        $dateTo = convert_to_server_timezone(date('Y-m-d H:i:s', strtotime($request->get('endDate'))));

        $kpis = Kpi::addSelect(DB::raw('CONCAT("week", (FLOOR((DAYOFMONTH(kpis.date) - 1) / 7) + 1)) AS week_of_month')) 
                    ->whereBetween('kpis.date', [$dateFrom, $dateTo])
                    ->where('employee_id', $employeeId)->orderBy('kpis.date', 'asc')->get();

        $weeks = [];
        foreach ($kpis as $kpi) {
            array_push($weeks, $kpi->week_of_month);
        }
        $weeks = array_unique($weeks);
        $weeks = array_filter($weeks);

        $currentEmployee = Employee::findOrFail($employeeId);
        $currentEmployeeUser = $currentEmployee->user;
        $currentEmployeeUserBranches = null;

        if ($currentEmployeeUser) {
            $currentEmployeeUserBranches = $currentEmployeeUser->branches;
        }

        $employeeIdArea = [];
        $employeeIdNational = [];

        if ($currentEmployeeUserBranches && count($currentEmployeeUserBranches) > 0) {
            $listBranches = [];

            foreach ($currentEmployeeUserBranches as $branch) {
                array_push($listBranches, $branch->id);
            }

            $employees = Employee::all();

            foreach ($employees as $emp) {
                $empUser = $emp->user;

                if ($empUser) {
                    $empUserBranches = $empUser->branches;

                    if ($empUserBranches && count($empUserBranches) > 0) {
                        $listEmpBranches = [];

                        foreach ($empUserBranches as $empBranch) {
                            array_push($listEmpBranches, $empBranch->id);
                        }

                        if (array_intersect($listEmpBranches, $listBranches)) {
                            array_push($employeeIdArea, $emp->id);
                            array_push($employeeIdNational, $emp->id);
                        }
                        else {
                            array_push($employeeIdNational, $emp->id);
                        }
                    }
                }
            }
        }

        $employeeIdArea = array_unique($employeeIdArea);
        $employeeIdArea = array_filter($employeeIdArea);
        $employeeIdNational = array_unique($employeeIdNational);
        $employeeIdNational = array_filter($employeeIdNational);

        $employee_achievements = [];
        $employee_achievements['automated'] = [];
        $employee_achievements['total'] = [
            'weight' => 0,
            'week1' => 0,
            'week2' => 0,
            'week3' => 0,
            'week4' => 0,
            'week5' => 0,
        ];

        $additionalComponents = EmployeeSalaryAdditionalComponent::all();

        foreach ($additionalComponents as $key => $additionalComponent) {
            $employee_achievements['automated'][$additionalComponent->automated_code]['id'] = ++$key;
            $employee_achievements['automated'][$additionalComponent->automated_code]['name'] = $additionalComponent->name;
            $employee_achievements['automated'][$additionalComponent->automated_code]['weight'] = $additionalComponent->weight;
            $employee_achievements['total']['weight'] += $additionalComponent->weight;
        }

        foreach ($employee_achievements['automated'] as $key => &$achievement) {
            if ($key === 'balance') {
                if (in_array('week1', $weeks)) {
                    $achievement['week1'] = 100;
                } else {
                    $achievement['week1'] = 0;
                }

                if (in_array('week2', $weeks)) {
                    $achievement['week2'] = 100;
                } else {
                    $achievement['week2'] = 0;
                }

                if (in_array('week3', $weeks)) {
                    $achievement['week3'] = 100;
                } else {
                    $achievement['week3'] = 0;
                }

                if (in_array('week4', $weeks)) {
                    $achievement['week4'] = 100;
                } else {
                    $achievement['week4'] = 0;
                }

                if (in_array('week5', $weeks)) {
                    $achievement['week5'] = 100;
                } else {
                    $achievement['week5'] = 0;
                }                
            } else {
                $data = [
                    'score' => 0,
                    'target' => 0,
                ];

                $achievement['week1'] = $data;
                $achievement['week2'] = $data;
                $achievement['week3'] = $data;
                $achievement['week4'] = $data;
                $achievement['week5'] = $data;
            }
        }

        // Area & National Call, Effective Call & Value
        foreach ($employees as $employee) {
            $assessmentData = $this->assessment($request, $employee->id);
            $assessmentData = $assessmentData['data']['indicators'];

            foreach ($assessmentData as $assessment) {
                if (array_key_exists('automated_code', $assessment)) {
                    if ($assessment['automated_code'] === 'C') {
                        if (in_array($employee->id, $employeeIdArea)) {
                            if (array_key_exists('achievement_area_call', $employee_achievements['automated'])) {
                                foreach ($employee_achievements['automated']['achievement_area_call'] as $week => &$data) {
                                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }
                        }

                        if (in_array($employee->id, $employeeIdNational)) {
                            if (array_key_exists('achievement_national_call', $employee_achievements['automated'])) {
                                foreach ($employee_achievements['automated']['achievement_national_call'] as $week => &$data) {
                                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }
                        }
                    } elseif ($assessment['automated_code'] === 'EC') {
                        if (array_key_exists('achievement_area_effective_call', $employee_achievements['automated'])) {
                            if (in_array($employee->id, $employeeIdArea)) {
                                foreach ($employee_achievements['automated']['achievement_area_effective_call'] as $week => &$data) {
                                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }
                        }

                        if (in_array($employee->id, $employeeIdNational)) {
                            if (array_key_exists('achievement_national_effective_call', $employee_achievements['automated'])) {
                                foreach ($employee_achievements['automated']['achievement_national_effective_call'] as $week => &$data) {
                                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }
                        }
                    } elseif ($assessment['automated_code'] === 'V') {
                        if (in_array($employee->id, $employeeIdArea)) {
                            if (array_key_exists('achievement_area_value', $employee_achievements['automated'])) {
                                foreach ($employee_achievements['automated']['achievement_area_value'] as $week => &$data) {
                                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }
                        }

                        if (in_array($employee->id, $employeeIdNational)) {
                            if (array_key_exists('achievement_national_value', $employee_achievements['automated'])) {
                                foreach ($employee_achievements['automated']['achievement_national_value'] as $week => &$data) {
                                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($employee_achievements['automated'] as $key => &$achievement) {
            if (stripos($key, 'area') !== false || stripos($key, 'national') !== false) {
                foreach ($achievement as $week => $score) {
                    if ($week !== 'weight' && $week !== 'name' && $week !== 'id') {
                        $achievement[$week] = $achievement[$week]['target'] ? $achievement[$week]['score'] / $achievement[$week]['target'] * 100 : 0;

                        if ($achievement[$week] > 100 && stripos($key, 'pelunasan piutang') === false) {
                            $achievement[$week] = 100;
                        }
                    }
                }
            }
        }

        return ['data' => $employee_achievements];
    }
}
