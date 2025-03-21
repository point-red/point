<?php

namespace App\Http\Requests\HumanResource\Employee\EmployeeJobLocation;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeJobLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:tenant.employee_job_locations',
            'area_values' => 'required|array',
            'area_values.*.year' => [
            'required',
            'integer',
            'min:1900',
        ],
            'area_values.*.value' => 'required|numeric',
            'area_values.*.notes' => 'nullable|string',
            'multiplier_kpi' => 'required',
        ];
    }

     /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'area_values.*.year.required' => 'The Year field is required.',
            'area_values.*.year.integer' => 'The Year must be a number.',
            'area_values.*.year.min' => 'The Year must be at least 1900.',

            'area_values.*.value.required' => 'The Value field is required.',
            'area_values.*.value.numeric' => 'The Value must be a number.',
        ];
    }
}
