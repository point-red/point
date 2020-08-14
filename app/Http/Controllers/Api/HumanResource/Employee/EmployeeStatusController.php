<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\Employee\EmployeeStatus\StoreEmployeeStatusRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeStatus\UpdateEmployeeStatusRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\HumanResource\Employee\EmployeeStatus\EmployeeStatusResource;
use App\Model\HumanResource\Employee\EmployeeStatus;
use Illuminate\Http\Request;

class EmployeeStatusController extends Controller
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
        $statuses = EmployeeStatus::eloquentFilter($request)
            ->select('employee_statuses.*');

        $statuses = pagination($statuses, $request->get('limit'));

        return new ApiCollection($statuses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeStatus\StoreEmployeeStatusRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeStatus\EmployeeStatusResource
     */
    public function store(StoreEmployeeStatusRequest $request)
    {
        $employeeStatus = new EmployeeStatus();
        $employeeStatus->name = $request->input('name');
        $employeeStatus->save();

        return new EmployeeStatusResource($employeeStatus);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function show($id)
    {
        $templates = EmployeeStatus::select('employee_statuses.*')
            ->where('employee_statuses.id', $id)
            ->first();

        return new ApiResource($templates);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeStatus\UpdateEmployeeStatusRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeStatus\EmployeeStatusResource
     */
    public function update(UpdateEmployeeStatusRequest $request, $id)
    {
        $employeeStatus = EmployeeStatus::findOrFail($id);
        $employeeStatus->name = $request->input('name');
        $employeeStatus->save();

        return new EmployeeStatusResource($employeeStatus);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeStatus\EmployeeStatusResource
     */
    public function destroy($id)
    {
        $employeeStatus = EmployeeStatus::findOrFail($id);

        $employeeStatus->delete();

        return new EmployeeStatusResource($employeeStatus);
    }
}
