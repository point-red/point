<?php

namespace App\Http\Requests\HumanResource\JobValue\JobValueCriteria;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobValueCriteriaRequest extends FormRequest
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
            'category' => 'required|unique:tenant.job_value_criterias',
            'criteria_factor' => 'required',
            'scales' => 'required|array',
            'scales.*.description' => 'required',
            'scales.*.value' => 'required|numeric',
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
            'scales.*.description.required' => 'Description field is required.',

            'scales.*.value.required' => 'The Value field is required.',
            'scales.*.value.numeric' => 'The Value must be a number.',
        ];
    }
}
