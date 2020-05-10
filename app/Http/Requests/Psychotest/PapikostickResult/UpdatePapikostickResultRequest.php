<?php

namespace App\Http\Requests\Psychotest\PapikostickResult;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePapikostickResultRequest extends FormRequest
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
            'total' => ['required', 'integer'],
            'papikostick_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer']
        ];
    }
}
