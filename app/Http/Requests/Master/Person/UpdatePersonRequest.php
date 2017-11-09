<?php

namespace App\Http\Requests\Master\Person;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

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
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => [
                'required',
                'unique:persons,name,'.$this->id.',id'
                .',person_categories_id,'.$request->get('person_categories_id'),
            ],
            'code' => 'unique:persons,code,'.$this->id,
            'person_categories_id' => 'required',
        ];
    }
}
