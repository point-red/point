<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Model\CloudStorage;
use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Model\Project\Project;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $additionalSalaryData = $employeeSalary->getAdditionalSalaryData($employeeSalary->assessments, $employeeSalary->achievements);
        $calculatedSalaryData = $employeeSalary->getCalculationSalaryData($additionalSalaryData);

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
        Storage::disk(env('STORAGE_DISK'))->put($path, $pdf);

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
}
