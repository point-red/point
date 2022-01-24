<?php

namespace App\Http\Requests\Accounting\CutOff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (env('APP_ENV') === 'testing') {
            return true;
        }

        return tenant(auth()->user()->id)->hasPermissionTo('update cut off');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
