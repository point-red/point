<?php

namespace App\Http\Requests\Psychotest\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'phone' => ['required', 'numeric'],
            'position_id' => ['required', 'numeric'],

            'is_password_used' => ['boolean'],

            'is_kraepelin_started' => ['boolean'],
            'is_kraepelin_finished' => ['boolean'],

            'is_papikostick_started' => ['boolean'],
            'current_papikostick_index' => ['numeric'],
            'is_papikostick_finished' => ['boolean'],

            'level' => [],
            'ktp_number' => [],
            'place_of_birth' => [],
            'date_of_birth' => [],
            'sex' => [],
            'religion' => [],
            'marital_status' => []
        ];
    }
}
