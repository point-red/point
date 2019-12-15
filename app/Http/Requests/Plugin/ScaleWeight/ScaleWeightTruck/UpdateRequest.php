<?php

namespace App\Http\Requests\Plugin\ScaleWeight\ScaleWeightTruck;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'uuid' => 'unique:tenant.scale_weight_trucks,uuid,'.$this->id,
            'machine_code' => 'required',
            'form_number' => 'required',
            'vendor' => 'required',
            'driver' => 'required',
            'license_number' => 'required',
            'item' => 'required',
            'gross_weight' => 'required|numeric|min:1',
            'tare_weight' => 'required|numeric|min:0',
            'net_weight' => 'required|numeric|min:1',
            'time_in' => 'required',
            'time_out' => 'required',
        ];
    }
}
