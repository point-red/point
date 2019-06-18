<?php

namespace App\Http\Requests\Sales\SalesOrder\SalesOrder;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrderRequest extends FormRequest
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

        $rulesSalesOrder = [
            'customer_id' => ValidationRule::foreignKey('customers'),
            'customer_name' => 'required|string',
            'eta' => 'date',
            'cash_only' => 'boolean',
            'need_down_payment' => ValidationRule::needDownPayment(),
            'delivery_fee' => ValidationRule::deliveryFee(),
            'discount_percent' => ValidationRule::discountPercent(),
            'discount_value' => ValidationRule::discountValue(),
            'type_of_tax' => ValidationRule::typeOfTax(),
            'tax' => ValidationRule::tax(),

            'sales_quotation_id' => ValidationRule::foreignKeyNullable('sales_quotations'),
            'sales_contract_id' => ValidationRule::foreignKeyNullable('sales_contracts'),
            'warehouse_id' => ValidationRule::foreignKeyNullable('warehouses'),

            'items' => 'required_without:services|array',
            'services' => 'required_without:items|array',
        ];

        $rulesSalesOrderItems = [
            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.discount_value' => ValidationRule::discountValue(),
            'items.*.discount_percent' => ValidationRule::discountPercent(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.taxable' => 'boolean',
            'items.*.allocation_code' => 'required_with:items.*.allocation_name|string|nullable',
            'items.*.allocation_name' => 'required_with:items.*.allocation_code|string',

            'items.*.sales_quotation_item_id' => ValidationRule::foreignKeyNullable('sales_quotation_items'),
            'items.*.sales_contract_item_id' => ValidationRule::foreignKeyNullable('sales_contract_items'),
            'items.*.sales_contract_group_item_id' => ValidationRule::foreignKeyNullable('sales_contract_group_items'),
        ];

        $rulesSalesOrderServices = [
            'services.*.service_id' => ValidationRule::foreignKey('services'),
            'services.*.service_name' => 'required|string',
            'services.*.quantity' => ValidationRule::quantity(),
            'services.*.discount_value' => ValidationRule::discountValue(),
            'services.*.discount_percent' => ValidationRule::discountPercent(),
            'services.*.taxable' => 'boolean',
            'services.*.allocation_code' => 'required_with:services.*.allocation_name|string|nullable',
            'services.*.allocation_name' => 'required_with:services.*.allocation_code|string',

            'services.*.sales_quotation_service_id' => ValidationRule::foreignKeyNullable('sales_quotation_services'),
        ];

        return array_merge($rulesForm, $rulesSalesOrder, $rulesSalesOrderItems, $rulesSalesOrderServices);
    }
}
