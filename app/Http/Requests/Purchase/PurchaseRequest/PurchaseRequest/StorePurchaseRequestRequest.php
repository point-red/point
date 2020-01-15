<?php

namespace App\Http\Requests\Purchase\PurchaseRequest\PurchaseRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StorePurchaseRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! tenant(auth()->user()->id)->hasPermissionTo('create purchase request')) {
            return false;
        }

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
        $validations = [
            'date' => 'required',
            'required_date' => 'required',
            'items.0.item_id' => 'required',
            'items.0.quantity' => 'required',
            'items.0.unit' => 'required',
        ];

        if ($request->has('items')) {
            $validations = array_merge($validations, [
                'items.*.item_id' => 'required',
                'items.*.quantity' => 'required',
                'items.*.unit' => 'required',
            ]);
        }

        if ($request->has('services')) {
            $validations = array_merge($validations, [
                'services.*.service_id' => 'required',
                'services.*.quantity' => 'required',
            ]);
        }

        return $validations;
    }
}
