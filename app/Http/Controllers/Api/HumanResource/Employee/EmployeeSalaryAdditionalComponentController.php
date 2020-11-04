<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\Employee\EmployeeSalaryAdditionalComponent\StoreEmployeeSalaryAdditionalComponentRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeSalaryAdditionalComponent\UpdateEmployeeSalaryAdditionalComponentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalaryAdditionalComponent\EmployeeSalaryAdditionalComponentResource;
use App\Model\HumanResource\Employee\EmployeeSalaryAdditionalComponent;
use Illuminate\Http\Request;

class EmployeeSalaryAdditionalComponentController extends Controller
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
        $additional_components = EmployeeSalaryAdditionalComponent::eloquentFilter($request)
            ->select('employee_salary_additional_components.*');

        $additional_components = pagination($additional_components, $request->get('limit'));

        return new ApiCollection($additional_components);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeSalaryAdditionalComponent\StoreEmployeeSalaryAdditionalComponentRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeSalaryAdditionalComponent\EmployeeSalaryAdditionalComponentResource
     */
    public function store(StoreEmployeeSalaryAdditionalComponentRequest $request)
    {
        $weight = $request->input('weight');

        $additionalComponent = new EmployeeSalaryAdditionalComponent();
        $additionalComponent->name = $request->input('name');
        $additionalComponent->weight = $weight;
        $additionalComponent->automated_code = $request->input('automated_code');
        $additionalComponent->automated_code_name = $request->input('automated_code_name');
        $additionalComponent->save();

        return new EmployeeSalaryAdditionalComponentResource($additionalComponent);
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
        $additionalComponent = EmployeeSalaryAdditionalComponent::select('employee_salary_additional_components.*')
            ->where('employee_salary_additional_components.id', $id)
            ->first();

        return new ApiResource($additionalComponent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeSalaryAdditionalComponent\UpdateEmployeeSalaryAdditionalComponentRequest $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeSalaryAdditionalComponent\EmployeeSalaryAdditionalComponentResource
     */
    public function update(UpdateEmployeeSalaryAdditionalComponentRequest $request, $id)
    {
        $additionalComponent = EmployeeSalaryAdditionalComponent::findOrFail($id);

        $weight = $request->input('weight');

        $additionalComponent->name = $request->input('name');
        $additionalComponent->weight = $weight;
        $additionalComponent->automated_code = $request->input('automated_code');
        $additionalComponent->automated_code_name = $request->input('automated_code_name');
        $additionalComponent->save();

        return new EmployeeSalaryAdditionalComponentResource($additionalComponent);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeSalaryAdditionalComponent\EmployeeSalaryAdditionalComponentResource
     */
    public function destroy($id)
    {
        $additionalComponent = EmployeeSalaryAdditionalComponent::findOrFail($id);

        $additionalComponent->delete();

        return response(null, 204);
    }
}
