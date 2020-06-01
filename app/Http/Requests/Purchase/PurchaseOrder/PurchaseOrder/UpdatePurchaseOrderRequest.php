<?php

namespace App\Http\Requests\Purchase\PurchaseOrder\PurchaseOrder;

use App\Http\Requests\ValidationRule;
use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
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

        $rulesPurchaseOrder = [
            'purchase_request_id' => ValidationRule::foreignKeyNullable('purchase_requests'),
            'purchase_contract_id' => ValidationRule::foreignKeyNullable('purchase_contracts'),
            'supplier_id' => ValidationRule::foreignKey(Supplier::getTableName()),
            'supplier_name' => 'required|string',
            'warehouse_id' => ValidationRule::foreignKeyNullable(Warehouse::getTableName()),
            'eta' => 'date',
            'cash_only' => 'boolean',
            'need_down_payment' => ValidationRule::needDownPayment(),
            'delivery_fee' => ValidationRule::deliveryFee(),
            'discount_percent' => ValidationRule::discountPercent(),
            'discount_value' => ValidationRule::discountValue(),
            'type_of_tax' => ValidationRule::typeOfTax(),
            'tax' => ValidationRule::tax(),
            'items' => 'required',
        ];

        $rulesPurchaseOrderItems = [
            'items.*.purchase_request_item_id' => ValidationRule::foreignKeyNullable(PurchaseRequestItem::getTableName()),
            'items.*.item_id' => ValidationRule::foreignKey(Item::getTableName()),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.price' => ValidationRule::price(),
            'items.*.discount_percent' => ValidationRule::discountPercent(),
            'items.*.discount_value' => ValidationRule::discountValue(),
            'items.*.taxable' => 'boolean',
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.allocation_id' => ValidationRule::foreignKeyNullable(Allocation::getTableName()),
        ];

        return array_merge($rulesForm, $rulesPurchaseOrder, $rulesPurchaseOrderItems);
    }
}
