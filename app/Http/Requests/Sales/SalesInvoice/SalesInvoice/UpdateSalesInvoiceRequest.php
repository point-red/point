<?php

namespace App\Http\Requests\Sales\SalesInvoice\SalesInvoice;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ValidationRule;

class UpdateSalesInvoiceRequest extends FormRequest
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

        $rulesSalesInvoice = [
            'customer_id' => ValidationRule::foreignKey('customers'),
            'customer_name' => 'required|string',
            'due_date' => 'required|date',
            'discount_value' => ValidationRule::discountValue(),
            'discount_percent' => ValidationRule::discountPercent(),
            'delivery_fee' => ValidationRule::deliveryFee(),
            'tax' => ValidationRule::tax(),
            'type_of_tax' => ValidationRule::typeOfTax(),

            'items' => 'required_without:services|array',
            'services' => 'required_without:items|array',
        ];

        $rulesSalesInvoiceItems = [
            'items.*.delivery_note_item_id' => ValidationRule::foreignKey('delivery_note_items'),
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.price' => ValidationRule::price(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.discount_value' => ValidationRule::discountValue(),
            'items.*.discount_percent' => ValidationRule::discountPercent(),
            'items.*.taxable' => 'boolean',
            'items.*.allocation_code' => 'required_with:items.*.allocation_name|string|nullable',
            'items.*.allocation_name' => 'required_with:items.*.allocation_code|string',
        ];

        $rulesSalesInvoiceServices = [
            'services.*.service_id' => ValidationRule::foreignKey('services'),
            'services.*.service_name' => 'required|string',
            'services.*.quantity' => ValidationRule::quantity(),
            'services.*.price' => ValidationRule::price(),
            'services.*.discount_value' => ValidationRule::discountValue(),
            'services.*.discount_percent' => ValidationRule::discountPercent(),
            'services.*.allocation_code' => 'required_with:services.*.allocation_name|string|nullable',
            'services.*.allocation_name' => 'required_with:services.*.allocation_code|string',
        ];

        // TODO validation for downpayment

        return array_merge($rulesForm, $rulesSalesInvoice, $rulesSalesInvoiceItems, $rulesSalesInvoiceServices);
    }
}
