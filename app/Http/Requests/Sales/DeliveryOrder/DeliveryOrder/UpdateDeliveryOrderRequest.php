<?php

namespace App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder;

use App\Http\Requests\ValidationRule;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryOrderRequest extends FormRequest
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
        $deliveryOrder = DeliveryOrder::find($this->id);
    
        $rulesForm = ValidationRule::form();
        $rulesForm['date'] = 'required|date|after_or_equal:'.$deliveryOrder->form->date;

        $rulesDeliveryOrder = [
            'sales_order_id' => ValidationRule::foreignKey('sales_orders'),
            'warehouse_id' => ValidationRule::foreignKeyNullable('warehouses'),

            'items' => 'required|array',
        ];

        $rulesDeliveryOrderItems = [
            'items.*.sales_order_item_id' => ValidationRule::foreignKey('sales_order_items'),
            'items.*.quantity_requested' => ValidationRule::quantity(),
            'items.*.quantity_delivered' => ValidationRule::quantity(),
            'items.*.quantity_remaining' => ValidationRule::quantity(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
        ];

        return array_merge($rulesForm, $rulesDeliveryOrder, $rulesDeliveryOrderItems);
    }
}
