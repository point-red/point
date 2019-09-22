<?php

namespace App\Http\Requests\HumanResource\Psychology\Kraeplin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKraeplinRequest extends FormRequest
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
            'candidate_id' => ['required'],
            'column_duration' => ['required'],
            'total_count' => [],
            'total_correct' => [],
            'active_column_id' => []
        ];
    }
}
