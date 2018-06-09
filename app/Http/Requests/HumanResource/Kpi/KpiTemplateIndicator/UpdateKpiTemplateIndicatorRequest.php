<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiTemplateIndicator;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKpiTemplateIndicatorRequest extends FormRequest
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
            'kpi_template_group_id' => 'required',
            'name' => 'required',
            'weight' => 'required',
            'target' => 'required',
        ];
    }
}
