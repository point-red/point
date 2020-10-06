<?php

namespace App\Http\Requests\Plugin\SalaryNonSales;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeFeeRequest extends FormRequest
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
            'employee_id' => 'required|exists:tenant.employees,id',
            'fee' => 'required|numeric',
            // 'score' => 'required|numeric',
            'start_period' => 'required|date',
            'end_period' => 'required|date',
            'criterias.*.factor_id' => 'required|exists:tenant.jobvalue_group_factors,id',
            'criterias.*.criteria_id' => 'required|exists:tenant.jobvalue_factor_criterias,id',
            'criterias.*.score' => 'required|numeric'
        ];
    }
}
