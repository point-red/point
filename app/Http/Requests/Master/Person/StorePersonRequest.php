<?php

namespace App\Http\Requests\Master\Person;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
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
                'unique:persons,name,NULL,id'
                    .',person_category_id,'.$request->get('person_category_id'),
            ],
            'code' => 'unique:persons,code',
            'person_category_id' => 'required',
        ];
    }
}
