<?php

namespace App\Http\Requests\Master\Item;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ValidationRule;

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
            'code' => 'bail|nullable|string|unique:items,code',
            'barcode' => 'bail|nullable|string|unique:items,barcode',
            'stock_reminder' => 'numeric|min:0',
            'taxable' => 'boolean',
            'units' => 'array',
            'groups' => 'nullable|array',

            // TODO each units and groups field
        ];
    }
}
