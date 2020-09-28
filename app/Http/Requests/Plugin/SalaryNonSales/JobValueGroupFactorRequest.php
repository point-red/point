<?php

namespace App\Http\Requests\Plugin\SalaryNonSales;

use Illuminate\Foundation\Http\FormRequest;

class JobValueGroupFactorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'group_id' => 'required|exists:tenant.jobvalue_groups,id'
        ];
    }
}