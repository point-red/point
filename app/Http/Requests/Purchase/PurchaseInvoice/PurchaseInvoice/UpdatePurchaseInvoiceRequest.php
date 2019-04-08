<?php

namespace App\Http\Requests\Purchase\PurchaseInvoice\PurchaseInvoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseInvoiceRequest extends FormRequest
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
        $ruleForm = [
            'date' => 'required|date',
        ];

        $rulePurchaseInvoice = [];

        $rulePurchaseInvoiceItems = [
            'item_name' => 'required|string',
        ];

        $rulePurchaseInvoiceServices = [
            'service_name' => 'required|string',
        ];

        return array_merge($ruleForm, $rulePurchaseInvoice, $rulePurchaseInvoiceItems, $rulePurchaseInvoiceServices);
    }
}
