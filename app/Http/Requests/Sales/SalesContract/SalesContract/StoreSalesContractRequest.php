<?php

namespace App\Http\Requests\Sales\SalesContract\SalesContract;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreSalesContractRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $array = [
            'customer_id' => 'bail|required|integer|min:1|exists:tenant.customers,id',
            'customer_name' => 'bail|string|min:1',
        ];

        if ($request->has('items')) {
            $array = array_merge($array, [
                'items.*.item_id' => 'bail|required|integer|min:1|exists:tenant.items,id',
                'items.*.price' => 'bail|required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'bail|required|numeric|min:0|not_in:0',
                'items.*.allocation_id' => 'bail|integer|min:1|exists:tenant.allocations,id',
            ]);
        }
        else {
            \Log::info('has groups');
            $array = array_merge($array, [
                'groups.*.group_id' => 'bail|required|integer|min:1|exists:tenant.groups,id',
                'groups.*.price' => 'bail|required|numeric|min:0|not_in:0',
                'groups.*.quantity' => 'bail|required|numeric|min:0|not_in:0',
                'groups.*.allocation_id' => 'bail|integer|min:1|exists:tenant.allocations,id',
            ]);
        }

        return $array;
    }
}
