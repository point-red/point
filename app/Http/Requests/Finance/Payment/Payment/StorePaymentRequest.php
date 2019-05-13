<?php

namespace App\Http\Requests\Finance\Payment\Payment;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ValidationRule;
use App\Model\Accounting\ChartOfAccount;

class StorePaymentRequest extends FormRequest
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

        $rulesPayment = [
            'payment_account_id' => ValidationRule::foreignKey(ChartOfAccount::getTableName()),
            'disbursed' => 'required|boolean',
            // TODO validate paymentable_id is exist
            'paymentable_id' => 'required|integer|min:0',
            'paymentable_type' => 'required|string',

            'details' => 'required|array',
        ];

        $rulesPaymentDetail = [
            'details.*.chart_of_account_id' => ValidationRule::foreignKey(ChartOfAccount::getTableName()),
            'details.*.amount' => ValidationRule::price(),
        ];

        return array_merge($rulesForm, $rulesPayment, $rulesPaymentDetail);
    }
}
