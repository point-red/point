<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Exports\Employee\EmployeeSalaryExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\HumanResource\Employee\EmployeeSalaryController;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use App\Model\CloudStorage;
use App\Model\Project\Project;

class EmployeeSalaryExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
          'id' => 'required|integer',
          'employeeId' => 'required|integer'
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
            . ' - Employee Salary Export - ' . $employeeSalary->employee->name . ' - ' . date('m Y', strtotime($employeeSalary->date));
        $fileExt = 'pdf';
        $path = 'tmp/' . $tenant . '/' . $key . '.' . $fileExt;

        $data = [
            'employeeSalary' => $employeeSalary,
            'additionalSalaryData' => $additionalSalaryData,
            'calculatedSalaryData' => $calculatedSalaryData
        ];

        $pdf = PDF::loadView('exports.human-resource.employee.salary', $data);
        $pdf = $pdf->setPaper('a4', 'portrait')->setWarnings(false);
        $pdf = $pdf->download()->getOriginalContent();
        Storage::put($path, $pdf) ;
        
        if (!$pdf) {
            return response()->json([
                'message' => 'Failed to export'
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
        $cloudStorage->download_url = env('API_URL') . '/download?key=' . $key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url
            ]
        ], 200);
    }

    private function getCalculationSalaryData($employeeSalary, $additionalSalaryData)
    {
        $salary_final_score_week_1 = ((double)$additionalSalaryData['total_assessments']['week1'] + (double)$additionalSalaryData['total_achievements']['week1']) / 2;
        $salary_final_score_week_2 = ((double)$additionalSalaryData['total_assessments']['week2'] + (double)$additionalSalaryData['total_achievements']['week2']) / 2;
        $salary_final_score_week_3 = ((double)$additionalSalaryData['total_assessments']['week3'] + (double)$additionalSalaryData['total_achievements']['week3']) / 2;
        $salary_final_score_week_4 = ((double)$additionalSalaryData['total_assessments']['week4'] + (double)$additionalSalaryData['total_achievements']['week4']) / 2;
        $salary_final_score_week_5 = ((double)$additionalSalaryData['total_assessments']['week5'] + (double)$additionalSalaryData['total_achievements']['week5']) / 2;

        $baseSalaryPerWeek = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->base_salary / $employeeSalary->active_days_in_month : 0;

        $base_salary_week_1 = $baseSalaryPerWeek * $employeeSalary->active_days_week1;
        $base_salary_week_2 = $baseSalaryPerWeek * $employeeSalary->active_days_week2;
        $base_salary_week_3 = $baseSalaryPerWeek * $employeeSalary->active_days_week3;
        $base_salary_week_4 = $baseSalaryPerWeek * $employeeSalary->active_days_week4;
        $base_salary_week_5 = $baseSalaryPerWeek * $employeeSalary->active_days_week5;

        $real_transport_allowance_week_1 = $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week1;
        $real_transport_allowance_week_2 = $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week2;
        $real_transport_allowance_week_3 = $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week3;
        $real_transport_allowance_week_4 = $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week4;
        $real_transport_allowance_week_5 = $employeeSalary->daily_transport_allowance * $employeeSalary->active_days_week5;

        $minimum_component_amount_week_1 = (double)$additionalSalaryData['total_assessments']['week1'] * $base_salary_week_1 / 100;
        $minimum_component_amount_week_2 = (double)$additionalSalaryData['total_assessments']['week2'] * $base_salary_week_2 / 100;
        $minimum_component_amount_week_3 = (double)$additionalSalaryData['total_assessments']['week3'] * $base_salary_week_3 / 100;
        $minimum_component_amount_week_4 = (double)$additionalSalaryData['total_assessments']['week4'] * $base_salary_week_4 / 100;
        $minimum_component_amount_week_5 = (double)$additionalSalaryData['total_assessments']['week5'] * $base_salary_week_5 / 100;

        $multiplier_kpi_week_1 = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week1 / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_2 = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week2 / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_3 = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week3 / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_4 = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week4 / $employeeSalary->active_days_in_month : 0;
        $multiplier_kpi_week_5 = $employeeSalary->active_days_in_month > 0 ? $employeeSalary->multiplier_kpi * $employeeSalary->active_days_week5 / $employeeSalary->active_days_in_month : 0;

        $additional_component_point_week_1 = (double)$additionalSalaryData['total_achievements']['week1'] * $multiplier_kpi_week_1 / 100;
        $additional_component_point_week_2 = (double)$additionalSalaryData['total_achievements']['week2'] * $multiplier_kpi_week_2 / 100;
        $additional_component_point_week_3 = (double)$additionalSalaryData['total_achievements']['week3'] * $multiplier_kpi_week_3 / 100;
        $additional_component_point_week_4 = (double)$additionalSalaryData['total_achievements']['week4'] * $multiplier_kpi_week_4 / 100;
        $additional_component_point_week_5 = (double)$additionalSalaryData['total_achievements']['week5'] * $multiplier_kpi_week_5 / 100;

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

        $total_amount_received_week_1 = $total_amount_week_1 - $employeeSalary->receiveable_cut_60_days_week1  + $employeeSalary->communication_allowance + $employeeSalary->team_leader_allowance;
        $total_amount_received_week_2 = $total_amount_week_2 - $employeeSalary->receiveable_cut_60_days_week2;
        $total_amount_received_week_3 = $total_amount_week_3 - $employeeSalary->receiveable_cut_60_days_week3;
        $total_amount_received_week_4 = $total_amount_week_4 - $employeeSalary->receiveable_cut_60_days_week4;
        $total_amount_received_week_5 = $total_amount_week_5 - $employeeSalary->receiveable_cut_60_days_week5;

        $total_amount_received = $total_amount_received_week_1 + $total_amount_received_week_2 + $total_amount_received_week_3 + $total_amount_received_week_4 + $total_amount_received_week_5;

        $company_profit_week_1 = (5 / 100) * ($employeeSalary->payment_from_marketing_week1 + $employeeSalary->payment_from_sales_week1 + $employeeSalary->payment_from_spg_week1);
        $company_profit_week_2 = (5 / 100) * ($employeeSalary->payment_from_marketing_week2 + $employeeSalary->payment_from_sales_week2 + $employeeSalary->payment_from_spg_week2);
        $company_profit_week_3 = (5 / 100) * ($employeeSalary->payment_from_marketing_week3 + $employeeSalary->payment_from_sales_week3 + $employeeSalary->payment_from_spg_week3);
        $company_profit_week_4 = (5 / 100) * ($employeeSalary->payment_from_marketing_week4 + $employeeSalary->payment_from_sales_week4 + $employeeSalary->payment_from_spg_week4);
        $company_profit_week_5 = (5 / 100) * ($employeeSalary->payment_from_marketing_week5 + $employeeSalary->payment_from_sales_week5 + $employeeSalary->payment_from_spg_week5);

        $settlement_difference_minus_amount_week_1 = $employeeSalary->payment_from_marketing_week1 + $employeeSalary->payment_from_sales_week1 + $employeeSalary->payment_from_spg_week1 - $total_amount_received_week_1;
        $settlement_difference_minus_amount_week_2 = $employeeSalary->payment_from_marketing_week2 + $employeeSalary->payment_from_sales_week2 + $employeeSalary->payment_from_spg_week2 - $total_amount_received_week_2;
        $settlement_difference_minus_amount_week_3 = $employeeSalary->payment_from_marketing_week3 + $employeeSalary->payment_from_sales_week3 + $employeeSalary->payment_from_spg_week3 - $total_amount_received_week_3;
        $settlement_difference_minus_amount_week_4 = $employeeSalary->payment_from_marketing_week4 + $employeeSalary->payment_from_sales_week4 + $employeeSalary->payment_from_spg_week4 - $total_amount_received_week_4;
        $settlement_difference_minus_amount_week_5 = $employeeSalary->payment_from_marketing_week5 + $employeeSalary->payment_from_sales_week5 + $employeeSalary->payment_from_spg_week5 - $total_amount_received_week_5;

        $company_profit_difference_minus_amount_week_1 = $company_profit_week_1 - $total_amount_received_week_1;
        $company_profit_difference_minus_amount_week_2 = $company_profit_week_2 - $total_amount_received_week_2;
        $company_profit_difference_minus_amount_week_3 = $company_profit_week_3 - $total_amount_received_week_3;
        $company_profit_difference_minus_amount_week_4 = $company_profit_week_4 - $total_amount_received_week_4;
        $company_profit_difference_minus_amount_week_5 = $company_profit_week_5 - $total_amount_received_week_5;

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
            'company_profit_difference_minus_amount_week_5' => $company_profit_difference_minus_amount_week_5
        ];
    }
}
