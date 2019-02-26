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
            'purchase' => 'required_without:sales',
            'sales' => 'required_without:purchase',
        ];

        /**
         * Purchasing
         */
        if ($request->has('supplier_id')) {
            $array = array_merge($array, [
                'purchase.supplier_id' => 'bail|integer|min:1|exists:tenant.suppliers,id',
                'purchase.date' => 'bail|required|date',
                'purchase.due_date' => 'bail|required|date',
                'purchase.delivery_fee' => 'bail|numeric|min:0',
                'purchase.discount_value' => 'bail|numeric|min:0',
                'purchase.type_of_tax' => 'bail|required|in:include,exclude,non',
                'purchase.tax' => 'bail|required|numeric|min:0',
                'purchase.discount_percent' => 'bail|nullable|numeric|min:0',
                'purchase.discount_value' => 'bail|numeric|min:0',
                'purchase.items' => 'required_without:purchase.services',
                'purchase.services' => 'required_without:purchase.items',
                
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
                'sales.customer_id' => 'bail|integer|min:1|exists:tenant.customers,id',
                'sales.date' => 'bail|required|date',
                'sales.eta' => 'bail|required|date',
                'sales.cash_only' => 'boolean',
                'sales.need_down_payment' => 'boolean',
                'sales.delivery_fee' => 'bail|numeric|min:0',
                'sales.discount_value' => 'bail|numeric|min:0',
                'sales.type_of_tax' => 'bail|required|in:include,exclude,non',
                'sales.tax' => 'bail|required|numeric|min:0',
                'sales.due_date' => 'bail|required|date',

                'sales.items' => 'required_without:sales.services',
                'sales.services' => 'required_without:sales.items',
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
                    'sales.services.*.service_id' => 'bail|required|integer|min:1|exists:tenant.services,id',
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
