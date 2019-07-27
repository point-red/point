<?php

namespace App\Http\Requests\Pos\PosBill;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePosBillRequest extends FormRequest
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
        $rulesForm = ValidationRule::form();

        $rulesPosBill = [
            'customer_id' => ValidationRule::foreignKeyNullable('customers'),
            'customer_name' => 'string',
            'discount_value' => ValidationRule::discountValue(),
            'discount_percent' => ValidationRule::discountPercent(),
            'tax' => ValidationRule::tax(),
            'type_of_tax' => ValidationRule::typeOfTax(),
            'amount' => ValidationRule::price(),
            'paid' => ValidationRule::price(),

            'items' => 'required_without:services|array',
        ];

        $rulesPosBillItems = [
            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.price' => ValidationRule::price(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.discount_value' => ValidationRule::discountValue(),
            'items.*.discount_percent' => ValidationRule::discountPercent(),
            'items.*.taxable' => 'boolean',
        ];

        return array_merge($rulesForm, $rulesPosBill, $rulesPosBillItems);
    }
}
