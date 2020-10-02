<?php

namespace App\Http\Controllers\Api\Plugin\SalaryNonSales;

use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource;
use App\Model\HumanResource\Employee\EmployeeJobLocation;
use Illuminate\Http\Request;

class JobLocationController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:tenant.employee_job_locations,name,' . $id,
            'base_salary' => 'required',
            'job_value' => 'required',
        ]);

        $employeeJobLocation = EmployeeJobLocation::findOrFail($id);
        $employeeJobLocation->name = $request->input('name');
        $employeeJobLocation->base_salary = $request->input('base_salary');
        $employeeJobLocation->job_value = $request->input('job_value');
        $employeeJobLocation->save();

        return new EmployeeJobLocationResource($employeeJobLocation);
    }
}
