<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreKpiGroupRequest extends FormRequest
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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => [
                'required',
                'unique:kpi_groups,name,NULL,id,kpi_category_id,'.$request->get('kpi_category_id'),
            ],
        ];
    }
}
