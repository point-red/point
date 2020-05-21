<?php

namespace App\Http\Requests\Sales\SalesDownPayment\SalesDownPayment;

use App\Http\Requests\ValidationRule;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesOrder\SalesOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesDownPaymentRequest extends FormRequest
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

        $rulesDownpayment = [
            'sales_order_id' => ValidationRule::foreignKeyNullable(SalesOrder::getTableName()).'|required_without:sales_contract_id',
            'sales_contract_id' => ValidationRule::foreignKeyNullable(SalesContract::getTableName()).'|required_without:sales_order_id',
            'amount' => ValidationRule::price(),
        ];

        $rulesPayment = [
            'allocation_id' => ValidationRule::foreignKeyNullable(Allocation::getTableName()),
            'payment_account_id' => ValidationRule::foreignKey(ChartOfAccount::getTableName()),
            'payment_number' => 'nullable|string',
        ];

        return array_merge($rulesForm, $rulesDownpayment, $rulesPayment);
    }
}
