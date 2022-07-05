<?php

namespace App\Http\Requests\Plugin\Study;

use Illuminate\Foundation\Http\FormRequest;

class StudySheetStoreRequest extends FormRequest
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
            'started_at' => 'required',
            'ended_at' => 'required',
            'subject_id' => 'required|exists:tenant.study_subjects,id',
            'competency' => 'required',
            'learning_goals' => 'required',
            'behavior' => 'required',
            'is_draft' => 'required',
        ];
    }
}
