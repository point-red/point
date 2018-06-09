<?php

namespace App\Http\Requests\Master\Person;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
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
                'unique:persons,name,'.$this->id.',id,person_category_id,'.$request->get('person_category_id'),
            ],
            'code' => 'unique:persons,code,'.$this->id,
            'person_category_id' => 'required',
        ];
    }
}
