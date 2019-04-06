<?php

namespace App\Http\Requests\Purchase\PurchaseOrder\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rulesForm = [
            'date' => 'required|date',
        ];

        $rulesPurchaseOrder = [
            'purchase_request_id' => ValidationRule::optionalForeignKey('purchase_requests'),
            'purchase_contract_id' => ValidationRule::optionalForeignKey('purchase_contracts'),

            'supplier_id' => ValidationRule::foreignKey('suppliers'),
            'supplier_name' => 'required|string',
            'warehouse_id' => 'nullable|'.ValidationRule::optionalForeignKey('warehouses'),
            'eta' => 'date',
            'cash_only' => 'boolean',
            'need_down_payment' => ValidationRule::needDownPayment(),
            'delivery_fee' => ValidationRule::deliveryFee(),
            'discount_percent' => ValidationRule::discountPercent(),
            'discount_value' => ValidationRule::discountValue(),
            'type_of_tax' => ValidationRule::typeOfTax(),
            'tax' => ValidationRule::tax(),

            'items' => 'required_without:services|array',
            'services' => 'required_without:items|array',
        ];

        $rulesPurchaseOrderItems = [
            'items.*.purchase_request_item_id' => ValidationRule::optionalForeignKey('items'),
            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percent' => ValidationRule::discountPercent(),
            'items.*.discount_value' => ValidationRule::discountValue(),
            'items.*.taxable' => 'boolean',
            'items.*.unit' => 'required|string',
            'items.*.converter' => 'required|numeric|min:0',
            'items.*.allocation_id' => ValidationRule::optionalForeignKey('allocations'),
        ];

        $rulesPurchaseOrderServices = [
            'services.*.purchase_request_item_id' => ValidationRule::optionalForeignKey('services'),
            'services.*.service_id' => ValidationRule::foreignKey('services'),
            'services.*.service_name' => 'required|string',
            'services.*.quantity' => 'required|numeric|min:0',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.discount_percent' => ValidationRule::discountPercent(),
            'services.*.discount_value' => ValidationRule::discountValue(),
            'services.*.taxable' => 'boolean',
            'services.*.allocation_id' => ValidationRule::optionalForeignKey('allocations'),
        ];

        return array_merge($rulesForm, $rulesPurchaseOrder, $rulesPurchaseOrderItems, $rulesPurchaseOrderServices);
    }
}
