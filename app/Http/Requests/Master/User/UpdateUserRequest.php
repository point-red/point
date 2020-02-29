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
            'first_name' => 'required|max:255|unique:tenant.users,first_name,'.$this->id,
            'last_name' => 'required|max:255|unique:tenant.users,last_name,'.$this->id,
            'address' => 'required|max:255|unique:tenant.users,address,'.$this->id,
            'phone' => 'required|max:255|unique:tenant.users,phone,'.$this->id,
            'email' => 'required|email|max:255|unique:tenant.users,email,'.$this->id,
        ];
    }
}
