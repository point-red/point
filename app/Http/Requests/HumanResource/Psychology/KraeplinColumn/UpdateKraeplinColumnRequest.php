<?php

namespace App\Http\Requests\HumanResource\Psychology\KraeplinColumn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKraeplinColumnRequest extends FormRequest
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
            'kraeplin_id' => ['required'],
            'current_first_number' => ['required'],
            'current_second_number' => ['required'],
            'correct' => ['required']
        ];
    }
}
