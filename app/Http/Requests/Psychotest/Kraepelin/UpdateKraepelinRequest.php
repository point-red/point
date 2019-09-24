<?php

namespace App\Http\Requests\Psychotest\Kraepelin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKraepelinRequest extends FormRequest
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
            'candidate_id' => ['required', 'numeric'],
            'total_count' => ['numeric'],
            'total_correct' => ['numeric'],
            'active_column_id' => ['numeric']
        ];
    }
}
