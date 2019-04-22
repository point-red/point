<?php

namespace App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
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

        $rulesDeliveryOrder = [
            'sales_order_id' => ValidationRule::foreignKey('sales_orders'),
            'warehouse_id' => ValidationRule::foreignKeyNullable('warehouses'),

            'items' => 'required|array',
        ];

        $rulesDeliveryOrderItems = [
            'items.*.item_id' => ValidationRule::foreignKey('items'),

            'items.*.sales_order_item_id' => ValidationRule::foreignKey('sales_order_items'),
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
        ];
    }
}
