<?php

namespace App\Http\Requests\Master\FixedAsset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixedAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(env("APP_ENV") == 'testing') {
            return true;
        }
        return tenant(auth()->user()->id)->hasPermissionTo('update fixed asset');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'fixed_asset_group_id' => 'nullable|numeric|exists:tenant.fixed_asset_groups,id',
        ];
    }
}
