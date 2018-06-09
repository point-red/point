<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiResult;

use Illuminate\Foundation\Http\FormRequest;

class StoreKpiResultRequest extends FormRequest
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
                'unique:kpi_results,score_min',
            ],
            'score_max' => [
                'required',
                'unique:kpi_results,score_max',
            ],
            'criteria' => [
                'required',
                'unique:kpi_results,criteria',
            ],
            'notes' => ['required'],
        ];
    }
}
