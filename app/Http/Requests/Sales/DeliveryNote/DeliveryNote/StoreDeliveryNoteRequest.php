<?php

namespace App\Http\Requests\Sales\DeliveryNote\DeliveryNote;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryNoteRequest extends FormRequest
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

        $rulesDeliveryNote = [
            'delivery_order_id' => ValidationRule::foreignKey('delivery_orders'),
            'warehouse_id' => ValidationRule::foreignKey('warehouses'),

            'items' => 'required|array',
        ];

        $rulesDeliveryNoteItems = [
            'items.*.delivery_order_item_id' => ValidationRule::foreignKey('delivery_order_items'),
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
        ];

        return array_merge($rulesForm, $rulesDeliveryNote, $rulesDeliveryNoteItems);
    }
}
