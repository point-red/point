<?php

namespace App\Http\Requests\HumanResource\Employee\EmployeeJobLocation;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeJobLocationRequest extends FormRequest
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
            'name' => 'required|unique:tenant.employee_job_locations',
        ];
    }
}
