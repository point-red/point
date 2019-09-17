<?php

namespace App\Http\Requests\Master\PricingGroup;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingGroupRequest extends FormRequest
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
            'label' => 'required|unique:tenant.pricing_groups,label'
        ];
    }
}
