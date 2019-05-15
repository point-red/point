<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\HumanResource\Kpi\KpiAutomatedController;
use App\Model\Project\Project;
use App\Model\Master\User;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessment;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentScore;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentTarget;
use App\Model\HumanResource\Employee\EmployeeSalaryAchievement;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiGroup;
use App\Model\HumanResource\Kpi\KpiIndicator;
use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;
use App\Http\Requests\HumanResource\Employee\EmployeeSalary\StoreEmployeeSalaryRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeSalary\UpdateEmployeeSalaryRequest;
use App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalary\EmployeeSalaryCollection;

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
        $employee_salaries = EmployeeSalary::where('employee_salaries.employee_id', $employeeId)
            ->orderBy('employee_salaries.start_date', 'asc')
            ->get();

        $startDates = [];
        $endDates = [];
        $scores = [];

        foreach ($employee_salaries as $key => $employee_salary) {
            $additionalData = $this->getAdditionalSalaryData($employee_salary)['additional'];

            $baseSalaryPerWeek = $employee_salary->active_days_in_month > 0 ? $employee_salary->base_salary / $employee_salary->active_days_in_month : 0;

            $base_salary_week_1 = $additionalData['score_percentages_assessments'][0]['week1'] ? $baseSalaryPerWeek * $employee_salary->active_days_week1 * ($additionalData['score_percentages_assessments'][0]['week1'] / 100) : 0;
            $base_salary_week_2 = $additionalData['score_percentages_assessments'][0]['week2'] ? $baseSalaryPerWeek * $employee_salary->active_days_week2 * ($additionalData['score_percentages_assessments'][0]['week2'] / 100) : 0;
            $base_salary_week_3 = $additionalData['score_percentages_assessments'][0]['week3'] ? $baseSalaryPerWeek * $employee_salary->active_days_week3 * ($additionalData['score_percentages_assessments'][0]['week3'] / 100) : 0;
            $base_salary_week_4 = $additionalData['score_percentages_assessments'][0]['week4'] ? $baseSalaryPerWeek * $employee_salary->active_days_week4 * ($additionalData['score_percentages_assessments'][0]['week4'] / 100) : 0;
            $base_salary_week_5 = $additionalData['score_percentages_assessments'][0]['week5'] ? $baseSalaryPerWeek * $employee_salary->active_days_week5 * ($additionalData['score_percentages_assessments'][0]['week5'] / 100) : 0;
            
            $real_transport_allowance_week_1 = $additionalData['score_percentages_assessments'][0]['week1'] ? $employee_salary->daily_transport_allowance * $employee_salary->active_days_week1 * ($additionalData['score_percentages_assessments'][0]['week1'] / 100) : 0;
            $real_transport_allowance_week_2 = $additionalData['score_percentages_assessments'][0]['week2'] ? $employee_salary->daily_transport_allowance * $employee_salary->active_days_week2 * ($additionalData['score_percentages_assessments'][0]['week2'] / 100) : 0;
            $real_transport_allowance_week_3 = $additionalData['score_percentages_assessments'][0]['week3'] ? $employee_salary->daily_transport_allowance * $employee_salary->active_days_week3 * ($additionalData['score_percentages_assessments'][0]['week3'] / 100) : 0;
            $real_transport_allowance_week_4 = $additionalData['score_percentages_assessments'][0]['week4'] ? $employee_salary->daily_transport_allowance * $employee_salary->active_days_week4 * ($additionalData['score_percentages_assessments'][0]['week4'] / 100) : 0;
            $real_transport_allowance_week_5 = $additionalData['score_percentages_assessments'][0]['week5'] ? $employee_salary->daily_transport_allowance * $employee_salary->active_days_week5 * ($additionalData['score_percentages_assessments'][0]['week5'] / 100) : 0;

            $minimum_component_amount_week_1 = (double)$additionalData['total_assessments']['week1'] * $base_salary_week_1 / 100;
            $minimum_component_amount_week_2 = (double)$additionalData['total_assessments']['week2'] * $base_salary_week_2 / 100;
            $minimum_component_amount_week_3 = (double)$additionalData['total_assessments']['week3'] * $base_salary_week_3 / 100;
            $minimum_component_amount_week_4 = (double)$additionalData['total_assessments']['week4'] * $base_salary_week_4 / 100;
            $minimum_component_amount_week_5 = (double)$additionalData['total_assessments']['week5'] * $base_salary_week_5 / 100;

            $multiplier_kpi_week_1 = $employee_salary->active_days_in_month > 0 && $additionalData['score_percentages_assessments'][0]['week1'] ? $employee_salary->multiplier_kpi * $employee_salary->active_days_week1 * ($additionalData['score_percentages_assessments'][0]['week1'] / 100) / $employee_salary->active_days_in_month : 0;
            $multiplier_kpi_week_2 = $employee_salary->active_days_in_month > 0 && $additionalData['score_percentages_assessments'][0]['week2'] ? $employee_salary->multiplier_kpi * $employee_salary->active_days_week2 * ($additionalData['score_percentages_assessments'][0]['week2'] / 100) / $employee_salary->active_days_in_month : 0;
            $multiplier_kpi_week_3 = $employee_salary->active_days_in_month > 0 && $additionalData['score_percentages_assessments'][0]['week3'] ? $employee_salary->multiplier_kpi * $employee_salary->active_days_week3 * ($additionalData['score_percentages_assessments'][0]['week3'] / 100) / $employee_salary->active_days_in_month : 0;
            $multiplier_kpi_week_4 = $employee_salary->active_days_in_month > 0 && $additionalData['score_percentages_assessments'][0]['week4'] ? $employee_salary->multiplier_kpi * $employee_salary->active_days_week4 * ($additionalData['score_percentages_assessments'][0]['week4'] / 100) / $employee_salary->active_days_in_month : 0;
            $multiplier_kpi_week_5 = $employee_salary->active_days_in_month > 0 && $additionalData['score_percentages_assessments'][0]['week5'] ? $employee_salary->multiplier_kpi * $employee_salary->active_days_week5 * ($additionalData['score_percentages_assessments'][0]['week5'] / 100) / $employee_salary->active_days_in_month : 0;

            $additional_component_point_week_1 = (double)$additionalData['total_achievements']['week1'] * $multiplier_kpi_week_1 / 100;
            $additional_component_point_week_2 = (double)$additionalData['total_achievements']['week2'] * $multiplier_kpi_week_2 / 100;
            $additional_component_point_week_3 = (double)$additionalData['total_achievements']['week3'] * $multiplier_kpi_week_3 / 100;
            $additional_component_point_week_4 = (double)$additionalData['total_achievements']['week4'] * $multiplier_kpi_week_4 / 100;
            $additional_component_point_week_5 = (double)$additionalData['total_achievements']['week5'] * $multiplier_kpi_week_5 / 100;

            $additional_component_amount_week_1 = $additional_component_point_week_1 * 1000;
            $additional_component_amount_week_2 = $additional_component_point_week_2 * 1000;
            $additional_component_amount_week_3 = $additional_component_point_week_3 * 1000;
            $additional_component_amount_week_4 = $additional_component_point_week_4 * 1000;
            $additional_component_amount_week_5 = $additional_component_point_week_5 * 1000;

            $total_component_amount_week_1 = $minimum_component_amount_week_1 + $additional_component_amount_week_1;
            $total_component_amount_week_2 = $minimum_component_amount_week_2 + $additional_component_amount_week_2;
            $total_component_amount_week_3 = $minimum_component_amount_week_3 + $additional_component_amount_week_3;
            $total_component_amount_week_4 = $minimum_component_amount_week_4 + $additional_component_amount_week_4;
            $total_component_amount_week_5 = $minimum_component_amount_week_5 + $additional_component_amount_week_5;

            $total_amount_week_1 = $total_component_amount_week_1 + $real_transport_allowance_week_1;
            $total_amount_week_2 = $total_component_amount_week_2 + $real_transport_allowance_week_2;
            $total_amount_week_3 = $total_component_amount_week_3 + $real_transport_allowance_week_3;
            $total_amount_week_4 = $total_component_amount_week_4 + $real_transport_allowance_week_4;
            $total_amount_week_5 = $total_component_amount_week_5 + $real_transport_allowance_week_5;

            $total_amount_received_week_1 = $total_amount_week_1  + $employee_salary->communication_allowance + $employee_salary->team_leader_allowance;
            $total_amount_received_week_2 = $total_amount_week_2;
            $total_amount_received_week_3 = $total_amount_week_3;
            $total_amount_received_week_4 = $total_amount_week_4;
            $total_amount_received_week_5 = $total_amount_week_5;

            $total_amount_received = $total_amount_received_week_1 + $total_amount_received_week_2 + $total_amount_received_week_3 + $total_amount_received_week_4 + $total_amount_received_week_5;

            array_push($startDates, date('d M Y', strtotime($employee_salary->start_date)));
            array_push($endDates, date('d F Y', strtotime($employee_salary->end_date)));
            array_push($scores, $total_amount_received);
        }

        return (new EmployeeSalaryCollection($employee_salaries))
            ->additional([
                'data_set' => [
                    'startDates' => $startDates,
                    'endDates' => $endDates,
                    'scores' => $scores,
                ],
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\Employee\StoreEmployeeSalaryRequest $request
     * @param                           $employeeId
     *
     * @return void
     */
    public function store(StoreEmployeeSalaryRequest $request, $employeeId)
    {
        if ($request->get('date') && count($request->get('date')) == 2)
        {
            $date = $request->get('date');

            $startDate = date('Y-m-d', strtotime($date['start']));
            $endDate = date('Y-m-d', strtotime($date['end']));

            $assessments = $request->get('salary_assessment');
            $achievements = $request->get('salary_achievement');

            DB::connection('tenant')->beginTransaction();

            $employee_salary = new EmployeeSalary;
            $employee_salary->employee_id = $employeeId;
            $employee_salary->job_location = $request->get('job_location');
            $employee_salary->start_date = $startDate;
            $employee_salary->end_date = $endDate;
            $employee_salary->base_salary = $request->get('base_salary');
            $employee_salary->multiplier_kpi = $request->get('multiplier_kpi');
            $employee_salary->daily_transport_allowance = $request->get('daily_transport_allowance');
            $employee_salary->team_leader_allowance = $request->get('team_leader_allowance');
            $employee_salary->communication_allowance = $request->get('communication_allowance');

            $employee_salary->active_days_in_month = $request->get('active_days_in_month') ?? 0;

            $employee_salary->active_days_week1 = $request->get('active_days_week_1') ?? 0;
            $employee_salary->active_days_week2 = $request->get('active_days_week_2') ?? 0;
            $employee_salary->active_days_week3 = $request->get('active_days_week_3') ?? 0;
            $employee_salary->active_days_week4 = $request->get('active_days_week_4') ?? 0;
            $employee_salary->active_days_week5 = $request->get('active_days_week_5') ?? 0;

            $employee_salary->receiveable_cut_60_days_week1 = $request->get('receiveable_cut_60_days_week_1') ?? 0;
            $employee_salary->receiveable_cut_60_days_week2 = $request->get('receiveable_cut_60_days_week_2') ?? 0;
            $employee_salary->receiveable_cut_60_days_week3 = $request->get('receiveable_cut_60_days_week_3') ?? 0;
            $employee_salary->receiveable_cut_60_days_week4 = $request->get('receiveable_cut_60_days_week_4') ?? 0;
            $employee_salary->receiveable_cut_60_days_week5 = $request->get('receiveable_cut_60_days_week_5') ?? 0;

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

            $employee_salary->cash_payment_week1 = $achievements['cash_payment']['week1'] ?? 0;
            $employee_salary->cash_payment_week2 = $achievements['cash_payment']['week2'] ?? 0;
            $employee_salary->cash_payment_week3 = $achievements['cash_payment']['week3'] ?? 0;
            $employee_salary->cash_payment_week4 = $achievements['cash_payment']['week4'] ?? 0;
            $employee_salary->cash_payment_week5 = $achievements['cash_payment']['week5'] ?? 0;

            $employee_salary->save();

            foreach ($assessments['indicators'] as $assessment) {
                $salaryAssessment = new EmployeeSalaryAssessment;
                $salaryAssessment->employee_salary_id = $employee_salary->id;
                $salaryAssessment->name = $assessment['name'];
                $salaryAssessment->weight = (double)$assessment['weight'];
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
                $salaryAchievement->name = $key;
                $salaryAchievement->weight = (double)$achievement['weight'];
                $salaryAchievement->week1 = array_key_exists('week1', $achievement) ? (double)$achievement['week1'] : 0;
                $salaryAchievement->week2 = array_key_exists('week2', $achievement) ? (double)$achievement['week2'] : 0;
                $salaryAchievement->week3 = array_key_exists('week3', $achievement) ? (double)$achievement['week3'] : 0;
                $salaryAchievement->week4 = array_key_exists('week4', $achievement) ? (double)$achievement['week4'] : 0;
                $salaryAchievement->week5 = array_key_exists('week5', $achievement) ? (double)$achievement['week5'] : 0;
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
            ->additional(
                $this->getAdditionalSalaryData($employee_salary)
            );
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

        $employee_salary->receiveable_cut_60_days_week1 = $salary['receiveable_cut_60_days_week1'] ?? 0;
        $employee_salary->receiveable_cut_60_days_week2 = $salary['receiveable_cut_60_days_week2'] ?? 0;
        $employee_salary->receiveable_cut_60_days_week3 = $salary['receiveable_cut_60_days_week3'] ?? 0;
        $employee_salary->receiveable_cut_60_days_week4 = $salary['receiveable_cut_60_days_week4'] ?? 0;
        $employee_salary->receiveable_cut_60_days_week5 = $salary['receiveable_cut_60_days_week5'] ?? 0;

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

        $employee_salary->save();

        foreach ($salary['assessments'] as $key => $assessment) {
            $salaryAssessment = EmployeeSalaryAssessment::findOrFail($assessment['id']);
            $salaryAssessment->weight = (double)$assessment['weight'];
            $salaryAssessment->save();
        }

        foreach ($salary['achievements'] as $key => $achievement) {
            $salaryAchievement = EmployeeSalaryAchievement::findOrFail($achievement['id']);
            $salaryAchievement->weight = (double)$achievement['weight'];
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

        return new EmployeeSalaryResource($employee_salary);
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
        $dateFrom = date('Y-m-d', strtotime($request->startDate));
        $dateTo = date('Y-m-d', strtotime($request->endDate));

        $kpis = Kpi::select('kpis.*')
            ->addSelect(DB::raw('CONCAT("week", (FLOOR((DAYOFMONTH(kpis.date) - 1) / 7) + 1)) AS week_of_month'))
            ->addSelect(DB::raw('DATE(kpis.date + INTERVAL (0 - WEEKDAY(kpis.date)) DAY) as first_day_of_week'))
            ->addSelect(DB::raw('DATE(kpis.date + INTERVAL (5 - WEEKDAY(kpis.date)) DAY) as last_day_of_week'));

        $kpis = $kpis->whereBetween('kpis.date', [$dateFrom, $dateTo]);
        $kpis = $kpis->where('employee_id', $employeeId)->orderBy('kpis.date', 'asc')->get();

        $kpi_automated_controller = new KpiAutomatedController();

        $employee_assessment = [
            'indicators' => []
        ];

        $indicatorIndex = 0;

        foreach ($kpis as $key => $kpi) {
            foreach ($kpi->groups as $key => $group) {
                foreach ($group->indicators as $key => $indicator) {
                    if (!array_key_exists($indicator->name, $employee_assessment['indicators'])) {
                        $indicator_data = [];
                        $indicator_data['id'] = ++$indicatorIndex;
                        $indicator_data['name'] = $indicator->name;
                        $indicator_data['weight'] = $indicator->weight;

                        if ($indicator->automated_id) {
                            $data = $kpi_automated_controller->getAutomatedData($indicator->automated_id, $kpi->first_day_of_week, $kpi->last_day_of_week, $employeeId);
                            $data_target = $kpi_automated_controller->getAutomatedData($indicator->automated_id, $kpi->last_day_of_week, $kpi->last_day_of_week, $employeeId);

                            $indicator->target = $data_target['target'];
                            $indicator->score = $data['score'];
                        }

                        $indicator_data['target'][$kpi->week_of_month] = $indicator->target;
                        $indicator_data['score'][$kpi->week_of_month] = $indicator->score;

                        if ($indicator->automated_id) {
                            $indicator_data['automated_id'] = $indicator->automated_id;
                            $indicator_data['employee_id'] = $kpi->employee_id;
                        }

                        $employee_assessment['indicators'][$indicator->name] = $indicator_data;
                    }
                    else {
                        $indicator_data = $employee_assessment['indicators'][$indicator->name];

                        if ($indicator->automated_id) {
                            $data = $kpi_automated_controller->getAutomatedData($indicator->automated_id, $kpi->first_day_of_week, $kpi->last_day_of_week, $employeeId);
                            $data_target = $kpi_automated_controller->getAutomatedData($indicator->automated_id, $kpi->last_day_of_week, $kpi->last_day_of_week, $employeeId);

                            $indicator->target = $data_target['target'];
                            $indicator->score = $data['score'];
                        }

                        if (!array_key_exists($kpi->week_of_month, $indicator_data['target'])) {
                            $indicator_data['target'][$kpi->week_of_month] = $indicator->target;
                        }
                        else {
                            if (!$indicator->automated_id) {
                                $indicator_data['target'][$kpi->week_of_month] += $indicator->target;
                            }
                        }

                        if (!array_key_exists($kpi->week_of_month, $indicator_data['score'])) {
                            $indicator_data['score'][$kpi->week_of_month] = $indicator->score;
                        }
                        else {
                            if (!$indicator->automated_id) {
                                $indicator_data['score'][$kpi->week_of_month] += $indicator->score;
                            }
                        }

                        $employee_assessment['indicators'][$indicator->name] = $indicator_data;
                    }
                }
            }
        }

        $returnable_array = [
            'indicators' => [],
            'total' => [
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0,
                'weight' => 0
            ]
        ];

        foreach ($employee_assessment['indicators'] as $key => $assessment) {
            foreach ($assessment['score'] as $week => $score) {
                $target = $employee_assessment['indicators'][$key]['target'][$week];
                $score = $employee_assessment['indicators'][$key]['score'][$week];

                $score_percentage = $target > 0 ? $score / $target * 100 : 0; 

                if ($score_percentage > 100 && stripos($key, 'value') === false) {
                    $score_percentage = 100;
                }

                $employee_assessment['indicators'][$key]['score_percentage'][$week] = $score_percentage;
            }
        }

        foreach ($employee_assessment['indicators'] as $key => $assessment) {
            $assessment['weight'] = (double)$assessment['weight'];
            array_push($returnable_array['indicators'], $assessment);
            $returnable_array['total']['weight'] += (double)$assessment['weight'];
        }

        foreach ($returnable_array['indicators'] as $assessment) {
            foreach ($assessment['score_percentage'] as $key => $score_percentage) {
                if (!array_key_exists($key, $returnable_array['total'])) {
                    $returnable_array['total'][$key] = (double)$score_percentage * $assessment['weight'] / 100;
                }
                else {
                    $returnable_array['total'][$key] += (double)$score_percentage * $assessment['weight'] / 100;
                }
            }
        }

        return ['data' => $returnable_array];
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
        // Project
        $project_code = $request->header('Tenant');
        $current_project = Project::where('code', $project_code)->first();

        if (!$project_code || !$current_project) {
            return response()->json([
                'code' => 422,
                'message' => 'Project not found',
            ], 422);
        }

        $group_of_projects = Project::where('group', $current_project->group)->get();

        $employee = Employee::findOrFail($employeeId);
        $userId = $employee->userEmployee->first()->id ?? 0;
        $user = User::findOrFail($userId);

        $dateFrom = date('Y-m-d', strtotime($request->startDate));
        $dateTo = date('Y-m-d', strtotime($request->endDate));

        $kpi_automated_controller = new KpiAutomatedController();

        $employee_achievements = [
            'automated' => [
                'balance' => [
                    'weight' => 0
                ],
                'achievement_national_call' => [
                    'weight' => 0
                ],
                'achievement_national_effective_call' => [
                    'weight' => 0
                ], 
                'achievement_national_value' => [
                    'weight' => 0
                ],
                'achievement_area_call' => [
                    'weight' => 0
                ],
                'achievement_area_effective_call' => [
                    'weight' => 0
                ], 
                'achievement_area_value' => [
                    'weight' => 0
                ],
            ],
            'cash_payment' => [
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0
            ],
            'total' => [
                'weight' => 0,
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0
            ]
        ];


        foreach ($employee_achievements['automated'] as $key => &$achievement) {
            if ($key !== 'balance') {
                $data = [
                    'score' => 0,
                    'target' => 0
                ];

                $achievement['week1'] = $data;
                $achievement['week2'] = $data;
                $achievement['week3'] = $data;
                $achievement['week4'] = $data;
                $achievement['week5'] = $data;
            }
        }


        // Area & National Call, Effective Call & Value
        foreach ($group_of_projects as $project) {
            config()->set('database.connections.tenant.database', 'point_' . strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $employees = Employee::all();

            foreach ($employees as $employee) {
                $assessmentData = $this->assessment($request, $employee->id);
                $assessmentData = $assessmentData['data']['indicators'];

                foreach ($assessmentData as $assessment) {
                    if (array_key_exists('automated_id', $assessment)) {
                        if ($assessment['automated_id'] === 'C') {
                            if ($project->code === $project_code) {
                                foreach ($employee_achievements['automated']['achievement_area_call'] as $week => &$data) {
                                    if ($week !== 'weight') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }

                            foreach ($employee_achievements['automated']['achievement_national_call'] as $week => &$data) {
                                if ($week !== 'weight') {
                                    $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                    $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                    $data['score'] += $score;
                                    $data['target'] += $target;
                                }
                            }
                        }
                        else if ($assessment['automated_id'] === 'EC') {
                            if ($project->code === $project_code) {
                                foreach ($employee_achievements['automated']['achievement_area_effective_call'] as $week => &$data) {
                                    if ($week !== 'weight') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;
                                    }
                                }
                            }

                            foreach ($employee_achievements['automated']['achievement_national_effective_call'] as $week => &$data) {
                                if ($week !== 'weight') {
                                    $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                    $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                    $data['score'] += $score;
                                    $data['target'] += $target;
                                }
                            }
                        }
                        else if ($assessment['automated_id'] === 'V') {
                            if ($project->code === $project_code) {
                                foreach ($employee_achievements['automated']['achievement_area_value'] as $week => &$data) {
                                    if ($week !== 'weight') {
                                        $score = array_key_exists($week, $assessment['score']) ? $assessment['score'][$week] : 0;
                                        $target = array_key_exists($week, $assessment['target']) ? $assessment['target'][$week] : 0;

                                        $data['score'] += $score;
                                        $data['target'] += $target;

                                        if ($score != 0) {
                                            $employee_achievements['automated']['balance'][$week] = 100;
                                        }
                                    }
                                }
                            }

                            foreach ($employee_achievements['automated']['achievement_national_value'] as $week => &$data) {
                                if ($week !== 'weight') {
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

            $dateWithTimeFrom = date('Y-m-d 00:00:00', strtotime($request->startDate));
            $dateWithTimeTo = date('Y-m-d 23:59:59', strtotime($request->endDate));

            $queryValueCashCredit = $this->queryValueCashCredit($dateWithTimeFrom, $dateWithTimeTo, $userId);

            // Cash payment_method
            if ($project->code === $project_code) {
                foreach ($queryValueCashCredit as $value) {
                    if (isset($value['week_of_month'])) {
                        if ($value['payment_method'] === 'cash') {
                            $employee_achievements['cash_payment'][$value['week_of_month']] += (double)$value['value'];
                        }
                    }
                }
            }
        }

        foreach ($employee_achievements['automated'] as $key => &$achievement) {
            if (stripos($key, 'area') !== false || stripos($key, 'national') !== false) {
                foreach ($achievement as $week => $score) {
                    if ($week !== 'weight') {
                        $achievement[$week] = $achievement[$week]['target'] ? $achievement[$week]['score'] / $achievement[$week]['target'] * 100: 0;

                        if ($achievement[$week] > 100 && stripos($key, 'value') === false) {
                            $achievement[$week] = 100;
                        }
                    }
                }
            }
        }

        config()->set('database.connections.tenant.database', 'point_' . strtolower($project_code));
        DB::connection('tenant')->reconnect();

        return ['data' => $employee_achievements];
    }

    private function queryValueCashCredit($dateFrom, $dateTo, $userId)
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->selectRaw('(quantity * price) as value')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('forms.created_by', $userId)
            ->addSelect('forms.created_by')
            ->addSelect('pin_point_sales_visitations.payment_method')
            ->addSelect(DB::raw('CONCAT("week", (FLOOR((DAYOFMONTH(forms.date) - 1) / 7) + 1)) AS week_of_month'))->get();
    }

    public function getAdditionalSalaryData($employee_salary)
    {
        $score_percentages_assessments = [];

        $total_assessments = [
            'weight' => 0,
            'week1' => 0,
            'week2' => 0,
            'week3' => 0,
            'week4' => 0,
            'week5' => 0
        ];

        $total_achievements = [
            'weight' => 0,
            'week1' => 0,
            'week2' => 0,
            'week3' => 0,
            'week4' => 0,
            'week5' => 0
        ];

        foreach ($employee_salary->assessments as $index => $indicator) {
            $score_percentages_assessments[$index] = [
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0
            ];

            foreach ($indicator->targets as $target) {
                $week_of_month = $target['week_of_month'];

                foreach ($indicator->scores as $score) {
                    if ($week_of_month === $score['week_of_month']) {
                        $score_percentages_assessments[$index][$week_of_month] = $target['target'] > 0 ? $score['score'] / $target['target'] * 100 : 0;

                        if ($score_percentages_assessments[$index][$week_of_month] > 100 && stripos($indicator->name, 'value') === false) {
                            $score_percentages_assessments[$index][$week_of_month] = 100;
                        }
                    }
                }
            }

            $total_assessments['weight'] += $indicator->weight;
        }

        foreach ($employee_salary->assessments as $index => $indicator) {
            foreach ($score_percentages_assessments[$index] as $week_of_month => $score_percentage) {
                $total_assessments[$week_of_month] += (double)$score_percentage * $indicator['weight'] / 100;
            }
        }

        foreach ($employee_salary->achievements as $achievement) {
            $total_achievements['week1'] += (double)$achievement->week1 * $achievement->weight / 100;
            $total_achievements['week2'] += (double)$achievement->week2 * $achievement->weight / 100;
            $total_achievements['week3'] += (double)$achievement->week3 * $achievement->weight / 100;
            $total_achievements['week4'] += (double)$achievement->week4 * $achievement->weight / 100;
            $total_achievements['week5'] += (double)$achievement->week5 * $achievement->weight / 100;
            $total_achievements['weight'] += $achievement->weight;            
        }

        return [
            'additional' => [
                'score_percentages_assessments' => $score_percentages_assessments,
                'total_assessments' => $total_assessments,
                'total_achievements' => $total_achievements
            ]
        ];
    }
}
