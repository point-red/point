<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiTemplate;

use Illuminate\Foundation\Http\FormRequest;

class StoreKpiTemplateRequest extends FormRequest
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
            'name' => 'required|unique:tenant.kpi_templates',
        ];
    }
}
