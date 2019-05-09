<?php

namespace App\Http\Requests\Purchase\PurchaseInvoice\PurchaseInvoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseInvoiceRequest extends FormRequest
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

        $rulesPurchaseInvoice = [
            'due_date' => 'required|date',
            'discount_value' => ValidationRule::discountValue(),
            'discount_percent' => ValidationRule::discountPercent(),
            'delivery_fee' => ValidationRule::deliveryFee(),
            'tax' => ValidationRule::tax(),
            'type_of_tax' => ValidationRule::typeOfTax(),
            'supplier_id' => ValidationRule::foreignKey('suppliers'),
            'supplier_name' => 'required|string',

            'items' => 'required_without:services|array',
            'services' => 'required_without:items|array',
        ];

        $rulesPurchaseInvoiceItems = [
            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.price' => ValidationRule::price(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.discount_value' => ValidationRule::discountValue(),
            'items.*.discount_percent' => ValidationRule::discountPercent(),
            'items.*.taxable' => 'boolean',
            'items.*.allocation_id' => ValidationRule::foreignKeyNullable('allocations'),
        ];

        $rulesPurchaseInvoiceServices = [
            'services.*.service_id' => ValidationRule::foreignKey('services'),
            'services.*.service_name' => 'required|string',
            'services.*.quantity' => ValidationRule::quantity(),
            'services.*.price' => ValidationRule::price(),
            'services.*.discount_value' => ValidationRule::discountValue(),
            'services.*.discount_percent' => ValidationRule::discountPercent(),
            'services.*.allocation_id' => ValidationRule::foreignKeyNullable('allocations'),
        ];

        return array_merge($rulesForm, $rulesPurchaseInvoice, $rulesPurchaseInvoiceItems, $rulesPurchaseInvoiceServices);
    }
}
