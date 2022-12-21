<?php

namespace App\Http\Requests\Sales\SalesReturn\SalesReturn;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesReturnRequest extends FormRequest
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

        $rulesSalesReturn = [
            'sales_invoice_id' => ValidationRule::foreignKey('sales_invoices'),
            'items' => 'required|array',
            'sub_total' => 'required|numeric|min:0',
            'tax_base' => 'required|numeric|min:0',
            'type_of_tax' => ValidationRule::typeOfTax(),
            'tax' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
        ];

        $rulesSalesReturnItems = [
            'items.*.sales_invoice_item_id' => ValidationRule::foreignKey('sales_invoice_items'),
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => ValidationRule::quantity(),
            'items.*.quantity_sales' => ValidationRule::quantity(),
            'items.*.unit' => ValidationRule::unit(),
            'items.*.converter' => ValidationRule::converter(),
            'items.*.total' => 'required|numeric|min:0',
        ];

        return array_merge($rulesForm, $rulesSalesReturn, $rulesSalesReturnItems);
    }
}
