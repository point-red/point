<?php

namespace App\Http\Requests\Master\PersonGroup;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonGroupRequest extends FormRequest
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
            'code' => 'required|unique:tenant.person_groups,code,'.$this->id,
            'name' => 'required|unique:tenant.person_groups,name,'.$this->id,
        ];
    }
}
