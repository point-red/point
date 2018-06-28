<?php

namespace App\Http\Requests\HumanResource\Kpi\Kpi;

use Illuminate\Foundation\Http\FormRequest;

class StoreKpiIndicatorRequest extends FormRequest
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
            'kpi_group_id' => 'required',
            'name' => 'required',
            'weight' => 'required',
            'target' => 'required',
            'score' => 'required',
            'score_percentage' => 'required',
            'score_description' => 'required',
        ];
    }
}
