<?php

namespace App\Http\Requests\Plugin\Study;

use Illuminate\Foundation\Http\FormRequest;

class StudySheetRequest extends FormRequest
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
            'started_at' => 'required_if:is_draft,false',
            'ended_at' => 'required_if:is_draft,false',
            'photo' => 'nullable|image',
            'audio' => 'nullable|mimetypes:mp3,m4a',
            'video' => 'nullable|mimetypes:mp4,mov',
            'subject_id' => 'required_if:is_draft,false|exists:tenant.study_subjects,id',
            'institution' => 'nullable|string|max:180',
            'teacher' => 'nullable|string|max:180',
            'competency' => 'required_if:is_draft,false|string|max:180',
            'learning_goals' => 'required_if:is_draft,false|string|max:180',
            'activities' => 'nullable|string|max:180',
            'grade' => 'nullable|integer|max:100',
            'behavior' => 'required_if:is_draft,false|string|max:1|in:A,B,C',
            'remarks' => 'nullable|string|max:180',
            'is_draft' => 'required|boolean',
        ];
    }
}
