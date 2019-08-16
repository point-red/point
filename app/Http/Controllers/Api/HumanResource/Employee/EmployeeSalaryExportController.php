<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use Carbon\Carbon;
use App\Model\CloudStorage;
use Illuminate\Http\Request;
use App\Model\Project\Project;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Model\HumanResource\Employee\EmployeeSalary;

class EmployeeSalaryExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
          'id' => 'required|integer',
          'employeeId' => 'required|integer',
        ]);

        $employeeSalary = EmployeeSalary::where('employee_salaries.employee_id', $request->get('employeeId'))
              ->where('employee_salaries.id', $request->get('id'))
              ->first();

        $employee_salary_controller = new EmployeeSalaryController();
        $additionalSalaryData = $employee_salary_controller->getAdditionalSalaryData($employeeSalary)['additional'];
        $calculatedSalaryData = $this->getCalculationSalaryData($employeeSalary, $additionalSalaryData);

        $tenant = strtolower($request->header('Tenant'));
        $key = str_random(16);
        $fileName = strtoupper($tenant)
            .' - Employee Salary Export - '.$employeeSalary->employee->name.' - '.date('d m Y', strtotime($employeeSalary->start_date)).' to '.date('d m Y', strtotime($employeeSalary->end_date));
        $fileExt = 'pdf';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

        $data = [
            'employeeSalary' => $employeeSalary,
            'additionalSalaryData' => $additionalSalaryData,
            'calculatedSalaryData' => $calculatedSalaryData,
        ];

        $pdf = PDF::loadView('exports.human-resource.employee.salary', $data);
        $pdf = $pdf->setPaper('a4', 'portrait')->setWarnings(false);
        $pdf = $pdf->download()->getOriginalContent();
        Storage::put($path, $pdf);

        if (! $pdf) {
            return response()->json([
                'message' => 'Failed to export',
            ], 422);
        }

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'employee salary';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
        $cloudStorage->owner_id = 1;
        $cloudStorage->expired_at = Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url,
            ],
        ], 200);
    }

    private function getCalculationSalaryData($employeeSalary, $additionalSalaryData)
    {
        $salary_final_score_week_1 = ((float) $additionalSalaryData['total_assessments']['week1'] + (float) $additionalSalaryData['total_achievements']['week1']) / 2;
        $salary_final_score_week_2 = ((float) $additionalSalaryData['total_assessments']['week2'] + (float) $additionalSalaryData['total_achievements']['week2']) / 2;
        $salary_final_score_week_3 = ((float) $additionalSalaryData['total_assessments']['week3'] + (float) $additionalSalaryData['total_achievements']['week3']) / 2;
        $salary_final_score_week_4 = ((float) $additionalSalaryData['total_assessments']['week4'] + (float) $additionalSalaryData['total_achievements']['week4']) / 2;
        $salary_final_score_week_5 = ((float) $additionalSalaryData['total_assessments']['week5'] + (float) $additionalSalaryData['total_achievements']['week5']) / 2;

        $baseSalaryPerWeek = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->base_salary / $employeeSalary->active_days_in_month : 0;

        $base_salary_week_1 = $additionalSalaryData['score_percentages_assessments'][0]['week1'] ? $baseSalaryPerWeek * $employeeSalary->active_days_week1 * ($additionalSalaryData['score_percentages_assessments'][0]['week1'] / 100) : 0;
        $base_salary_week_2 = $additionalSalaryData['score_percentages_assessments'][0]['week2'] ? $baseSalaryPerWeek * $employeeSalary->active_days_week2 * ($additionalSalaryData['score_percentages_assessments'][0]['week2'] / 100) : 0;
        $base_salary_week_3 = $additionalSalaryData['score_percentages_assessments'][0]['week3'] ? $baseSalaryPerWeek * $employeeSalary->active_days_week3 * ($additionalSalaryData['score_percentages_assessments'][0]['week3'] / 100) : 0;
        $base_salary_week_4 = $additionalSalaryData['score_percentages_assessments'][0]['week4'] ? $baseSalaryPerWeek * $employeeSalary->active_days_week4 * ($additionalSalaryData['score_percentages_assessments'][0]['week4'] / 100) : 0;
        $base_salary_week_5 = $additionalSalaryData['score_percentages_assessments'][0]['week5'] ? $baseSalaryPerWeek * $employeeSalary->active_days_week5 * ($additionalSalaryData['score_percentages_assessments'][0]['week5'] / 100) : 0;

        $real_transport_allowance_week_1 = $additionalSalaryData['score_percentages_assessments'][0]['week1'] ? $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week1 * ($additionalSalaryData['score_percentages_assessments'][0]['week1'] / 100) : 0;
        $real_transport_allowance_week_2 = $additionalSalaryData['score_percentages_assessments'][0]['week2'] ? $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week2 * ($additionalSalaryData['score_percentages_assessments'][0]['week2'] / 100) : 0;
        $real_transport_allowance_week_3 = $additionalSalaryData['score_percentages_assessments'][0]['week3'] ? $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week3 * ($additionalSalaryData['score_percentages_assessments'][0]['week3'] / 100) : 0;
        $real_transport_allowance_week_4 = $additionalSalaryData['score_percentages_assessments'][0]['week4'] ? $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week4 * ($additionalSalaryData['score_percentages_assessments'][0]['week4'] / 100) : 0;
        $real_transport_allowance_week_5 = $additionalSalaryData['score_percentages_assessments'][0]['week5'] ? $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week5 * ($additionalSalaryData['score_percentages_assessments'][0]['week5'] / 100) : 0;

        $minimum_component_amount_week_1 = (float) $additionalSalaryData['total_assessments']['week1'] * $base_salary_week_1 / 100;
        $minimum_component_amount_week_2 = (float) $additionalSalaryData['total_assessments']['week2'] * $base_salary_week_2 / 100;
        $minimum_component_amount_week_3 = (float) $additionalSalaryData['total_assessments']['week3'] * $base_salary_week_3 / 100;
        $minimum_component_amount_week_4 = (float) $additionalSalaryData['total_assessments']['week4'] * $base_salary_week_4 / 100;
        $minimum_component_amount_week_5 = (float) $additionalSalaryData['total_assessments']['week5'] * $base_salary_week_5 / 100;

        $multiplier_kpi_week_1 = $employeeSalary->active_days_in_month > 0 && $additionalSalaryData['score_percentages_assessments'][0]['week1'] ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week1 * ($additionalSalaryData['score_percentages_assessments'][0]['week1'] / 100) / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_2 = $employeeSalary->active_days_in_month > 0 && $additionalSalaryData['score_percentages_assessments'][0]['week2'] ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week2 * ($additionalSalaryData['score_percentages_assessments'][0]['week2'] / 100) / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_3 = $employeeSalary->active_days_in_month > 0 && $additionalSalaryData['score_percentages_assessments'][0]['week3'] ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week3 * ($additionalSalaryData['score_percentages_assessments'][0]['week3'] / 100) / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_4 = $employeeSalary->active_days_in_month > 0 && $additionalSalaryData['score_percentages_assessments'][0]['week4'] ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week4 * ($additionalSalaryData['score_percentages_assessments'][0]['week4'] / 100) / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_5 = $employeeSalary->active_days_in_month > 0 && $additionalSalaryData['score_percentages_assessments'][0]['week5'] ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week5 * ($additionalSalaryData['score_percentages_assessments'][0]['week5'] / 100) / $employeeSalary->active_days_in_month : 0;

        $additional_component_point_week_1 = (float) $additionalSalaryData['total_achievements']['week1'] * $multiplier_kpi_week_1 / 100;
        $additional_component_point_week_2 = (float) $additionalSalaryData['total_achievements']['week2'] * $multiplier_kpi_week_2 / 100;
        $additional_component_point_week_3 = (float) $additionalSalaryData['total_achievements']['week3'] * $multiplier_kpi_week_3 / 100;
        $additional_component_point_week_4 = (float) $additionalSalaryData['total_achievements']['week4'] * $multiplier_kpi_week_4 / 100;
        $additional_component_point_week_5 = (float) $additionalSalaryData['total_achievements']['week5'] * $multiplier_kpi_week_5 / 100;

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

        $total_amount_received_week_1 = $total_amount_week_1 + $employeeSalary->communication_allowance + $employeeSalary->functional_allowance;
        $total_amount_received_week_2 = $total_amount_week_2;
        $total_amount_received_week_3 = $total_amount_week_3;
        $total_amount_received_week_4 = $total_amount_week_4;
        $total_amount_received_week_5 = $total_amount_week_5;

        $total_amount_received = $total_amount_received_week_1 + $total_amount_received_week_2 + $total_amount_received_week_3 + $total_amount_received_week_4 + $total_amount_received_week_5;

        $company_profit_week_1 = 0.05 * ($employeeSalary->payment_from_marketing_week1 + $employeeSalary->payment_from_sales_week1 + $employeeSalary->payment_from_spg_week1 + $employeeSalary->cash_payment_week1);
        $company_profit_week_2 = 0.05 * ($employeeSalary->payment_from_marketing_week2 + $employeeSalary->payment_from_sales_week2 + $employeeSalary->payment_from_spg_week2 + $employeeSalary->cash_payment_week2);
        $company_profit_week_3 = 0.05 * ($employeeSalary->payment_from_marketing_week3 + $employeeSalary->payment_from_sales_week3 + $employeeSalary->payment_from_spg_week3 + $employeeSalary->cash_payment_week3);
        $company_profit_week_4 = 0.05 * ($employeeSalary->payment_from_marketing_week4 + $employeeSalary->payment_from_sales_week4 + $employeeSalary->payment_from_spg_week4 + $employeeSalary->cash_payment_week4);
        $company_profit_week_5 = 0.05 * ($employeeSalary->payment_from_marketing_week5 + $employeeSalary->payment_from_sales_week5 + $employeeSalary->payment_from_spg_week5 + $employeeSalary->cash_payment_week5);

        $settlement_difference_minus_amount_week_1 = $employeeSalary->payment_from_marketing_week1 + $employeeSalary->payment_from_sales_week1 + $employeeSalary->payment_from_spg_week1 + $employeeSalary->cash_payment_week1 - $total_amount_received_week_1;
        $settlement_difference_minus_amount_week_2 = $employeeSalary->payment_from_marketing_week2 + $employeeSalary->payment_from_sales_week2 + $employeeSalary->payment_from_spg_week2 + $employeeSalary->cash_payment_week2 - $total_amount_received_week_2;
        $settlement_difference_minus_amount_week_3 = $employeeSalary->payment_from_marketing_week3 + $employeeSalary->payment_from_sales_week3 + $employeeSalary->payment_from_spg_week3 + $employeeSalary->cash_payment_week3 - $total_amount_received_week_3;
        $settlement_difference_minus_amount_week_4 = $employeeSalary->payment_from_marketing_week4 + $employeeSalary->payment_from_sales_week4 + $employeeSalary->payment_from_spg_week4 + $employeeSalary->cash_payment_week4 - $total_amount_received_week_4;
        $settlement_difference_minus_amount_week_5 = $employeeSalary->payment_from_marketing_week5 + $employeeSalary->payment_from_sales_week5 + $employeeSalary->payment_from_spg_week5 + $employeeSalary->cash_payment_week5 - $total_amount_received_week_5;

        $company_profit_difference_minus_amount_week_1 = $company_profit_week_1 - $total_amount_week_1;
        $company_profit_difference_minus_amount_week_2 = $company_profit_week_2 - $total_amount_week_2;
        $company_profit_difference_minus_amount_week_3 = $company_profit_week_3 - $total_amount_week_3;
        $company_profit_difference_minus_amount_week_4 = $company_profit_week_4 - $total_amount_week_4;
        $company_profit_difference_minus_amount_week_5 = $company_profit_week_5 - $total_amount_week_5;

        $day_average_divisor = 0;
        $total_minimum_component_score = 0;
        $total_additional_component_score = 0;
        $total_final_score = 0;
        $average_minimum_component_score = 0;
        $average_additional_component_score = 0;
        $average_final_score = 0;

        if ($employeeSalary->active_days_week1 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week1'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week1'];
            $total_final_score += $salary_final_score_week_1;
        }
        if ($employeeSalary->active_days_week2 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week2'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week2'];
            $total_final_score += $salary_final_score_week_2;
        }
        if ($employeeSalary->active_days_week3 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week3'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week3'];
            $total_final_score += $salary_final_score_week_3;
        }
        if ($employeeSalary->active_days_week4 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week4'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week4'];
            $total_final_score += $salary_final_score_week_4;
        }
        if ($employeeSalary->active_days_week5 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week5'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week5'];
            $total_final_score += $salary_final_score_week_5;
        }

        $average_minimum_component_score = $day_average_divisor != 0 ? $total_minimum_component_score / $day_average_divisor : 0;
        $average_additional_component_score = $day_average_divisor != 0 ? $total_additional_component_score / $day_average_divisor : 0;
        $average_final_score = $day_average_divisor != 0 ? $total_final_score / $day_average_divisor : 0;

        $total_payment = ($employeeSalary->payment_from_marketing_week1 + $employeeSalary->payment_from_sales_week1 + $employeeSalary->payment_from_spg_week1 + $employeeSalary->cash_payment_week1) + ($employeeSalary->payment_from_marketing_week2 + $employeeSalary->payment_from_sales_week2 + $employeeSalary->payment_from_spg_week2 + $employeeSalary->cash_payment_week2) + ($employeeSalary->payment_from_marketing_week3 + $employeeSalary->payment_from_sales_week3 + $employeeSalary->payment_from_spg_week3 + $employeeSalary->cash_payment_week3) + ($employeeSalary->payment_from_marketing_week4 + $employeeSalary->payment_from_sales_week4 + $employeeSalary->payment_from_spg_week4 + $employeeSalary->cash_payment_week4) + ($employeeSalary->payment_from_marketing_week5 + $employeeSalary->payment_from_sales_week5 + $employeeSalary->payment_from_spg_week5 + $employeeSalary->cash_payment_week5);

        $total_settlement_difference_minus_amount = $settlement_difference_minus_amount_week_1 + $settlement_difference_minus_amount_week_2 + $settlement_difference_minus_amount_week_3 + $settlement_difference_minus_amount_week_4 + $settlement_difference_minus_amount_week_5;

        $total_company_profit_difference_minus_amount = $company_profit_difference_minus_amount_week_1 + $company_profit_difference_minus_amount_week_2 + $company_profit_difference_minus_amount_week_3 + $company_profit_difference_minus_amount_week_4 + $company_profit_difference_minus_amount_week_5;

        $total_weekly_sales = $employeeSalary->weekly_sales_week1 + $employeeSalary->weekly_sales_week2 + $employeeSalary->weekly_sales_week3 + $employeeSalary->weekly_sales_week4 + $employeeSalary->weekly_sales_week5;

        $total_amount_received_difference = $employeeSalary->maximum_salary_amount - $total_amount_received;

        return [
            'salary_final_score_week_1' => $salary_final_score_week_1,
            'salary_final_score_week_2' => $salary_final_score_week_2,
            'salary_final_score_week_3' => $salary_final_score_week_3,
            'salary_final_score_week_4' => $salary_final_score_week_4,
            'salary_final_score_week_5' => $salary_final_score_week_5,
            'minimum_component_amount_week_1' => $minimum_component_amount_week_1,
            'minimum_component_amount_week_2' => $minimum_component_amount_week_2,
            'minimum_component_amount_week_3' => $minimum_component_amount_week_3,
            'minimum_component_amount_week_4' => $minimum_component_amount_week_4,
            'minimum_component_amount_week_5' => $minimum_component_amount_week_5,
            'additional_component_amount_week_1' => $additional_component_amount_week_1,
            'additional_component_amount_week_2' => $additional_component_amount_week_2,
            'additional_component_amount_week_3' => $additional_component_amount_week_3,
            'additional_component_amount_week_4' => $additional_component_amount_week_4,
            'additional_component_amount_week_5' => $additional_component_amount_week_5,
            'total_component_amount_week_1' => $total_component_amount_week_1,
            'total_component_amount_week_2' => $total_component_amount_week_2,
            'total_component_amount_week_3' => $total_component_amount_week_3,
            'total_component_amount_week_4' => $total_component_amount_week_4,
            'total_component_amount_week_5' => $total_component_amount_week_5,
            'total_amount_week_1' => $total_amount_week_1,
            'total_amount_week_2' => $total_amount_week_2,
            'total_amount_week_3' => $total_amount_week_3,
            'total_amount_week_4' => $total_amount_week_4,
            'total_amount_week_5' => $total_amount_week_5,
            'total_amount_received_week_1' => $total_amount_received_week_1,
            'total_amount_received_week_2' => $total_amount_received_week_2,
            'total_amount_received_week_3' => $total_amount_received_week_3,
            'total_amount_received_week_4' => $total_amount_received_week_4,
            'total_amount_received_week_5' => $total_amount_received_week_5,
            'total_amount_received' => $total_amount_received,
            'company_profit_week_1' => $company_profit_week_1,
            'company_profit_week_2' => $company_profit_week_2,
            'company_profit_week_3' => $company_profit_week_3,
            'company_profit_week_4' => $company_profit_week_4,
            'company_profit_week_5' => $company_profit_week_5,
            'settlement_difference_minus_amount_week_1' => $settlement_difference_minus_amount_week_1,
            'settlement_difference_minus_amount_week_2' => $settlement_difference_minus_amount_week_2,
            'settlement_difference_minus_amount_week_3' => $settlement_difference_minus_amount_week_3,
            'settlement_difference_minus_amount_week_4' => $settlement_difference_minus_amount_week_4,
            'settlement_difference_minus_amount_week_5' => $settlement_difference_minus_amount_week_5,
            'company_profit_difference_minus_amount_week_1' => $company_profit_difference_minus_amount_week_1,
            'company_profit_difference_minus_amount_week_2' => $company_profit_difference_minus_amount_week_2,
            'company_profit_difference_minus_amount_week_3' => $company_profit_difference_minus_amount_week_3,
            'company_profit_difference_minus_amount_week_4' => $company_profit_difference_minus_amount_week_4,
            'company_profit_difference_minus_amount_week_5' => $company_profit_difference_minus_amount_week_5,
            'average_minimum_component_score' => $average_minimum_component_score,
            'average_additional_component_score' => $average_additional_component_score,
            'average_final_score' => $average_final_score,
            'total_payment' => $total_payment,
            'total_settlement_difference_minus_amount' => $total_settlement_difference_minus_amount,
            'total_company_profit_difference_minus_amount' => $total_company_profit_difference_minus_amount,
            'total_weekly_sales' => $total_weekly_sales,
            'total_amount_received_difference' => $total_amount_received_difference,
        ];
    }
}
