<?php

namespace App\Http\Requests\Master\User;

use App\Http\Requests\ApiFormRequest;

class UpdateUserRequest extends ApiFormRequest
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
            'name' => 'required|max:255|unique:tenant.users,'.$this->id,
            'email' => 'required|email|max:255|unique:tenant.users,'.$this->id,
        ];
    }
}
