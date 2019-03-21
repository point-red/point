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
            'date' => 'date_format:Y-m|required|unique:tenant.employee_salaries|unique:tenant.employee_salaries,employee_id',
            'job_location' => 'required',
            'base_salary' => 'required',
            'multiplier_kpi' => 'required',
            'daily_transport_allowance' => 'required',
            'team_leader_allowance' => 'required',
            'communication_allowance' => 'required',
            'active_days_in_month' => 'required'
        ];
    }
}
