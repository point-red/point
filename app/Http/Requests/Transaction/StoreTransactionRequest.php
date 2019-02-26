<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreTransactionRequest extends FormRequest
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
     * @param Request $request
     * @return array
     */
    public function rules(Request $request)
    {
        $array = [
            'date' => 'bail|required|date',
            '*.discount_percent' => 'bail|nullable|numeric|min:0',
            '*.discount_value' => 'bail|numeric|min:0',
            'items' => 'required_without:services',
            'services' => 'required_without:items',
        ];

        /**
         * Purchasing
         */
        if ($request->has('supplier_id')) {
            $array = array_merge($array, [
                'supplier_id' => 'bail|integer|min:1|exists:tenant.suppliers,id',
                'due_date' => 'bail|required|date',
                'delivery_fee' => 'bail|numeric|min:0',
                'discount_value' => 'bail|numeric|min:0',
                'type_of_tax' => 'bail|required|in:include,exclude,non',
                'tax' => 'bail|required|numeric|min:0',
            ]);

            if ($request->has('purchase.items')) {
                $array = array_merge($array, [
                    'purchase.items.*.item_id' => 'bail|required|integer|min:1|exists:tenant.items,id',
                    'purchase.items.*.gross_weight' => 'bail|nullable|numeric|min:0',
                    'purchase.items.*.tare_weight' => 'bail|nullable|numeric|min:0',
                    'purchase.items.*.net_weight' => 'bail|nullable|numeric|min:0',
                    'purchase.items.*.quantity' => 'bail|required|numeric|min:0',
                    'purchase.items.*.price' => 'bail|numeric|min:0',
                    'purchase.items.*.discount_percent' => 'bail|nullable|numeric|min:0',
                    'purchase.items.*.discount_value' => 'bail|numeric|min:0',
                    'purchase.items.*.allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                    'purchase.items.*.taxable' => 'bail|boolean',
                    'purchase.items.*.unit' => 'bail|required|string|max:255',
                    'purchase.items.*.converter' => 'bail|required|numeric|min:0',
                    'purchase.warehouse_id' => 'bail|required|integer|min:1|exists:tenant.warehouses,id',
                ]);
            }

            if ($request->has('purchase.services')) {
                $array = array_merge($array, [
                    'purchase.services.*.service_id' => 'bail|required|integer|min:1|exists:tenant.services,id',
                ]);
            }

            if ($request->has('purchase.payment')) {
                $array = array_merge($array, [
                    'purchase.payment.type' => 'bail|string|min:1',
                    'purchase.payment.chart_of_account_id' => 'bail|required|integer|min:1|exists:tenant.chart_of_accounts,id',
                    'purchase.payment.allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                ]);
            }
        }

        /**
         * Sales
         */
        if ($request->has('customer_id')) {
            $array = array_merge($array, [
                'customer_id' => 'bail|integer|min:1|exists:tenant.customers,id',
                'eta' => 'bail|required|date',
                'due_date' => 'bail|required|date',
                'cash_only' => 'boolean',
                'need_down_payment' => 'boolean',
                'delivery_fee' => 'bail|numeric|min:0',
                'discount_value' => 'bail|numeric|min:0',
                'type_of_tax' => 'bail|required|in:include,exclude,non',
                'tax' => 'bail|required|numeric|min:0',
            ]);

            if ($request->has('sales.items')) {
                $array = array_merge($array, [
                    'sales.items.*.item_id' => 'bail|required|integer|min:1|exists:tenant.items,id',
                    'sales.items.*.gross_weight' => 'bail|nullable|numeric|min:0',
                    'sales.items.*.tare_weight' => 'bail|nullable|numeric|min:0',
                    'sales.items.*.net_weight' => 'bail|nullable|numeric|min:0',
                    'sales.items.*.quantity' => 'bail|required|numeric|min:0',
                    'sales.items.*.price' => 'bail|numeric|min:0',
                    'sales.items.*.discount_percent' => 'bail|nullable|numeric|min:0',
                    'sales.items.*.discount_value' => 'bail|numeric|min:0',
                    'sales.items.*.allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                    'sales.items.*.taxable' => 'bail|boolean',
                    'sales.items.*.unit' => 'bail|required|string|max:255',
                    'sales.items.*.converter' => 'bail|required|numeric|min:0',
                    'sales.warehouse_id' => 'bail|required|integer|min:1|exists:tenant.warehouses,id',
                ]);
            }

            if ($request->has('sales.services')) {
                $array = array_merge($array, [
                    'purchase.services.*.service_id' => 'bail|required|integer|min:1|exists:tenant.services,id',
                ]);
            }

            if ($request->has('sales.payment')) {
                $array = array_merge($array, [
                    'sales.payment.type' => 'bail|string|min:1',
                    'sales.payment.chart_of_account_id' => 'bail|required|integer|min:1|exists:tenant.chart_of_accounts,id',
                    'sales.payment.allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                ]);
            }
        }

        return $array;
    }
}
