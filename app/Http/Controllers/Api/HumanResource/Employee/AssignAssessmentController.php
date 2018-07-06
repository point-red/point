<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource;
use App\Model\HumanResource\Employee\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AssignAssessmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param                           $employeeId
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource
     */
    public function store(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $employee->kpi_template_id = $request->get('kpi_template_id');
        $employee->save();

        return new EmployeeResource($employee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param                           $employeeId
     *
     * @return void
     */
    public function update(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $employee->kpi_template_id = $request->get('kpi_template_id');
        $employee->save();
    }
}
