<?php

namespace App\Http\Requests\Master\Item;

use App\Http\Requests\ValidationRule;
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
            'name' => 'string',
            'chart_of_account_id' => ValidationRule::foreignKeyOptional('chart_of_accounts'),
            'code' => 'bail|nullable|string|unique:tenant.items,code,'.$this->id,
            'barcode' => 'bail|nullable|string|unique:tenant.items,barcode',
            'stock_reminder' => 'numeric|min:0',
            'taxable' => 'boolean',
            'units' => 'array',
            'groups' => 'nullable|array',
        ];
    }
}
