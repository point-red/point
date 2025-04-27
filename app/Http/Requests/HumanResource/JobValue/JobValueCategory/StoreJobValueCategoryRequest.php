<?php

namespace App\Http\Requests\HumanResource\JobValue\JobValueCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobValueCategoryRequest extends FormRequest
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
            'category' => 'required|unique:tenant.job_value_categories'
        ];
    }
}
