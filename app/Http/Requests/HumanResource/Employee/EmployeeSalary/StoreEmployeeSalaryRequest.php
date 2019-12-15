<?php

namespace App\Http\Requests\HumanResource\Employee\EmployeeSalary;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeSalaryRequest extends FormRequest
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
            'date' => 'required',
            'job_location' => 'required',
            'base_salary' => 'required',
            'multiplier_kpi' => 'required',
            'daily_transport_allowance' => 'required',
            'functional_allowance' => 'required',
            'communication_allowance' => 'required',
            'active_days_in_month' => 'required',
        ];
    }
}
