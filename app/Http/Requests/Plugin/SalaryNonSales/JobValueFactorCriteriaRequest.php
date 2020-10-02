<?php

namespace App\Http\Requests\Plugin\SalaryNonSales;

use Illuminate\Foundation\Http\FormRequest;

class JobValueFactorCriteriaRequest extends FormRequest
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
            'level' => 'required',
            'description' => 'required',
            'score' => 'required',
            'factor_id' => 'required|exists:tenant.jobvalue_group_factors,id'
        ];
    }
}
