<?php

namespace App\Http\Requests\HumanResource\Employee\EmployeeSalaryAdditionalComponent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeSalaryAdditionalComponentRequest extends FormRequest
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
            'name' => 'required|unique:tenant.employee_salary_additional_components,name,'.$this->id,
            'weight' => 'required',
            'automated_code' => 'required|unique:tenant.employee_salary_additional_components,automated_code,'.$this->id,
            'automated_code_name' => 'required',
        ];
    }
}
