<?php

namespace App\Http\Requests\HumanResource\JobValue\JobValueScoreSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobValueScoreSettingRequest extends FormRequest
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
            'minimum_kpi' => 'required|numeric|min:0',
            'minimum_coc' => 'required|numeric|min:0',
        ];
    }
}
