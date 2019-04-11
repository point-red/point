<?php

namespace App\Http\Requests\Purchase\PurchaseReceive\PurchaseReceive;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseReceiveRequest extends FormRequest
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
        $ruleForm = ValidationRule::form();

        $rulePurchaseReceive = [
            'supplier_id' => ValidationRule::foreignKey('suppliers'),
            'supplier_name' => 'required|string',
            'warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'purchase_order_id' => ValidationRule::optionalForeignKey('purchase_orders'),

            'items' => 'required_without:services|array',
            'services' => 'required_without:items|array',
        ];

        $rulePurchaseReceiveItems = [
            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.item_name' => 'required|string',
            'items.*.purchase_order_item_id' => ValidationRule::foreignKey('purchase_order_items'),
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.unit' => ValidationRule::quantity(),
            'items.*.converter' => ValidationRule::quantity(),
            'items.*.allocation_id' => ValidationRule::optionalForeignKey('allocations'),
        ];

        $rulePurchaseReceiveServices = [
            'services.*.service_id' => ValidationRule::foreignKey('services'),
            'services.*.service_name' => 'required|string',
            'services.*.purchase_order_service_id' => ValidationRule::foreignKey('purchase_order_services'),
            'services.*.quantity' => ValidationRule::quantity(),
            'services.*.allocation_id' => ValidationRule::optionalForeignKey('allocations'),
        ];

        return array_merge($ruleForm, $rulePurchaseReceive, $rulePurchaseReceiveItems, $rulePurchaseReceiveServices);
    }
}
