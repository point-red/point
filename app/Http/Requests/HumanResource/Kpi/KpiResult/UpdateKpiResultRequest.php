<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiResult;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKpiResultRequest extends FormRequest
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
            'score_min' => [
                'required',
                Rule::unique('kpi_results')->ignore($this->id),
            ],
            'score_max' => [
                'required',
                Rule::unique('kpi_results')->ignore($this->id),
            ],
            'criteria' => [
                'required',
                Rule::unique('kpi_results')->ignore($this->id),
            ],
            'notes' => ['required'],
        ];
    }
}
