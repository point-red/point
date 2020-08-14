<?php

namespace App\Http\Requests\Master\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! tenant(auth()->user()->id)->hasPermissionTo('update item')) {
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
            'name' => 'required|string|unique:tenant.items,name,'.$this->id,
            'chart_of_account_id' => 'required',
            'code' => 'bail|nullable|string|unique:tenant.items,code,'.$this->id,
            'barcode' => 'bail|nullable|string|unique:tenant.items,barcode',
            'stock_reminder' => 'numeric|min:0',
            'taxable' => 'boolean',
            'units' => 'array',
            'groups' => 'nullable|array',
        ];
    }
}
