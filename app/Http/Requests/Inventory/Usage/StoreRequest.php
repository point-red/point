<?php

namespace App\Http\Requests\Inventory\Usage;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        return [
            'warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'employee_id' => ValidationRule::foreignKey('employees'),
            'request_approval_to' => 'required',
            'notes' => 'nullable|string|max:255',

            'items.*.item_id' => ValidationRule::foreignKey('items'),
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.chart_of_account_id' => ValidationRule::foreignKey('chart_of_accounts'),
        ];
    }
}
