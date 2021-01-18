<?php

namespace App\Http\Requests\Master\Expedition;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpeditionRequest extends FormRequest
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
            'code' => 'unique:tenant.expeditions,code,'.$this->id,
            'name' => 'required',
        ];
    }
}
