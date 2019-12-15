<?php

namespace App\Http\Requests\Master\Item;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @throws \Exception
     */
    public function authorize()
    {
        if (! tenant(auth()->user()->id)->hasPermissionTo('create item')) {
            return false;
        }

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
            'name' => 'required|string',
            'chart_of_account_id' => ValidationRule::foreignKey('chart_of_accounts'),
            'code' => 'bail|nullable|string|unique:tenant.items,code',
            'barcode' => 'bail|nullable|string|unique:tenant.items,barcode',
            'stock_reminder' => 'numeric|min:0',
            'taxable' => 'boolean',
            'units' => 'required|array',
            'groups' => 'array',
        ];
    }
}
