<?php

namespace App\Http\Requests\HumanResource\JobValue\JobValueAssessment;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobValueAssessmentRequest extends FormRequest
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
            'employee_id' => 'required|integer|exists:tenant.employees,id',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'status' => 'required|string|max:255',
            'approval_status' => 'nullable|string|max:255',
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
            'employee_id.required' => 'Employee is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'period_from.required' => 'The start period is required.',
            'period_to.required' => 'The end period is required.',
            'period_to.after_or_equal' => 'The end period must be after or equal to the start period.'
        ];
    }
}
