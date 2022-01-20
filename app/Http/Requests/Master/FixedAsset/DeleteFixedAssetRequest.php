<?php

namespace App\Http\Requests\Master\FixedAsset;

use Illuminate\Foundation\Http\FormRequest;

class DeleteFixedAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (env("APP_ENV") == 'testing') {
            return true;
        }
        return tenant(auth()->user()->id)->hasPermissionTo('delete fixed asset');
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
