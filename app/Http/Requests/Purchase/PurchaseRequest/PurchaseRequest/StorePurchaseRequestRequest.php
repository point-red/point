<?php

namespace App\Http\Requests\Purchase\PurchaseRequest\PurchaseRequest;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

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
            'employee_id' => 'required',
            'date' => 'required',
            'required_date' => 'required',
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
