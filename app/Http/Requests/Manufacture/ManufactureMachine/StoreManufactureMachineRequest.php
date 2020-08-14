<?php

namespace App\Http\Requests\Manufacture\ManufactureMachine;

use Illuminate\Foundation\Http\FormRequest;

class StoreManufactureMachineRequest extends FormRequest
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
            'code' => 'string|unique:tenant.manufacture_machines,code',
            'name' => 'required|string|unique:tenant.manufacture_machines,name',
        ];
    }
}
