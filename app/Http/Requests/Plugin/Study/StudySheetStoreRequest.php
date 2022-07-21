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
            'started_at' => 'required_if:is_draft,0',
            'ended_at' => 'required_if:is_draft,0',
            'photo' => 'nullable|image',
            'audio' => 'nullable|mimetypes:audio/*',
            'video' => 'nullable|mimetypes:video/*',
            'subject_id' => 'required_if:is_draft,0|exists:tenant.study_subjects,id',
            'institution' => 'nullable|string|max:180',
            'teacher' => 'nullable|string|max:180',
            'competency' => 'required_if:is_draft,0|string|max:180',
            'learning_goals' => 'required_if:is_draft,0|string|max:180',
            'activities' => 'nullable|string|max:180',
            'grade' => 'nullable|integer|max:100',
            'behavior' => 'required_if:is_draft,0|string|max:1|in:A,B,C',
            'remarks' => 'nullable|string|max:180',
            'is_draft' => 'required|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'started_at.required_if' => __('validation.required'),
            'ended_at.required_if' => __('validation.required'),
            'subject_id.required_if' => __('validation.required'),
            'competency.required_if' => __('validation.required'),
            'learning_goals.required_if' => __('validation.required'),
            'behavior.required_if' => __('validation.required'),
        ];
    }
}
