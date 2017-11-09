<?php

namespace App\Http\Requests\Master\PersonCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonCategoryRequest extends FormRequest
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
            'code' => 'required|unique:person_categories,code,' . $this->id,
            'name' => 'required|unique:person_categories,name,' . $this->id,
        ];
    }
}
