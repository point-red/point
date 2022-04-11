<?php

namespace App\Http\Requests\Inventory\TransferItem;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferItemRequest extends FormRequest
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

        $rulesTransferItem = [
            'warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'to_warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'driver' => 'required|string',

            'items' => 'required_without:services|array',
        ];

        $rulesTransferItemItems = [
            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter()
        ];

        return array_merge($rulesForm, $rulesTransferItem, $rulesTransferItemItems);
    }
}
