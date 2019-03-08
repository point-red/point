<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\HumanResource\Employee\EmployeeJobLocation;
use App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource;
use App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationCollection;
use App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\StoreEmployeeJobLocationRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\UpdateEmployeeJobLocationRequest;

class EmployeeJobLocationController extends Controller
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
        $job_locations = EmployeeJobLocation::select('employee_job_locations.*')
            ->filters($request->get('filters'))
            ->fields($request->get('fields'))
            ->sortBy($request->get('sort_by'))
            ->includes($request->get('includes'))
            ->paginate($request->get('paginate') ?? 20);
            
        return new ApiCollection($job_locations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\StoreEmployeeJobLocationRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource
     */
    public function store(StoreEmployeeJobLocationRequest $request)
    {
        $employeeJobLocation = new EmployeeJobLocation();
        $employeeJobLocation->name = $request->input('name');
        $employeeJobLocation->base_salary = $request->input('base_salary');
        $employeeJobLocation->multiplier_kpi = $request->input('multiplier_kpi');
        $employeeJobLocation->save();

        return new EmployeeJobLocationResource($employeeJobLocation);
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
        $templates = EmployeeJobLocation::select('employee_job_locations.*')
            ->where('employee_job_locations.id', $id)
            ->first();

        return new ApiResource($templates);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\UpdateEmployeeJobLocationRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource
     */
    public function update(UpdateEmployeeJobLocationRequest $request, $id)
    {
        $employeeJobLocation = EmployeeJobLocation::findOrFail($id);
        $employeeJobLocation->name = $request->input('name');
        $employeeJobLocation->base_salary = $request->input('base_salary');
        $employeeJobLocation->multiplier_kpi = $request->input('multiplier_kpi');
        $employeeJobLocation->save();

        return new EmployeeJobLocationResource($employeeJobLocation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource
     */
    public function destroy($id)
    {
        $employeeJobLocation = EmployeeJobLocation::findOrFail($id);

        $employeeJobLocation->delete();

        return new EmployeeJobLocationResource($employeeJobLocation);
    }
}
