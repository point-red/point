<?php

namespace App\Http\Requests\Master\FixedAsset;

use App\Model\Master\FixedAsset;
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
        if(env("APP_ENV") == "testing") return true;
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
            'name' => 'required|string|unique:tenant.fixed_assets,name,' . $this->id,
            'code' => 'required|string|unique:tenant.fixed_assets,code,' . $this->id,
            'fixed_asset_group_id' => 'nullable|numeric|exists:tenant.fixed_asset_groups,id',
            'depreciation_method' => 'required|string|in:'.implode(",", array_column(FixedAsset::getAllDepreciationMethods(), "id")),
            'chart_of_account_id' => 'required|numeric',
            'accumulation_chart_of_account_id' => 'required_if:depreciation_method,STRAIGHT_LINE|nullable|numeric',
            'depreciation_chart_of_account_id' => 'required_if:depreciation_method,STRAIGHT_LINE|nullable|numeric',
            'useful_life_year' => 'required_if:depreciation_method,STRAIGHT_LINE|nullable|numeric',
            'salvage_value' => 'required_if:depreciation_method,STRAIGHT_LINE|nullable|numeric',
        ];
    }
}
