<?php

namespace App\Http\Requests\Plugin\PlayBook\Glossary;

use Illuminate\Foundation\Http\FormRequest;

class StoreGlossaryRequest extends FormRequest
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
            'code' => ['required', 'unique:tenant.play_book_glossaries'],
            'name' => 'required',
            'abbreviation' => 'required',
        ];
    }
}
