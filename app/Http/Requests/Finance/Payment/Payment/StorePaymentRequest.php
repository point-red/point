<?php

namespace App\Http\Requests\Finance\Payment\Payment;

use App\Exceptions\PointException;
use App\Http\Requests\ValidationRule;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Master\Allocation;
use Illuminate\Foundation\Http\FormRequest;

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
            'paymentable_id' => 'required|integer|min:0',
            'paymentable_type' => 'required|string',
            'details' => 'required|array',
            'notes' => 'nullable|max:255'
        ];

        $rulesPaymentDetail = [
            'details.*.chart_of_account_id' => ValidationRule::foreignKey(ChartOfAccount::getTableName()),
            'details.*.amount' => ValidationRule::price(),
            'details.*.allocation_id' => ValidationRule::foreignKeyNullable(Allocation::getTableName()),
            'details.*.referenceable_type' => [
                function ($attribute, $value, $fail) {
                    if (!PaymentDetail::referenceableIsValid($value)) {
                        $fail($attribute . ' is invalid');
                    }
                },
            ],
        ];

        return array_merge($rulesForm, $rulesPayment, $rulesPaymentDetail);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Cash out/bank out
            if (request()->get('disbursed') == 1) {
                $amountCashAdvance = 0;
                $needToPayFromAccount = request()->get('amount');

                if ((request()->filled('cash_advance.id'))) {
                    $cashAdvance = CashAdvance::find(request()->get('cash_advance')['id']);
                    $amountCashAdvance = $cashAdvance->amount_remaining;
                    if ($amountCashAdvance > $needToPayFromAccount) {
                        // All covered by cash advance
                        $needToPayFromAccount = 0;
                    } else {
                        $needToPayFromAccount = request()->get('amount') - $amountCashAdvance;
                    }
                }

                if ($needToPayFromAccount > 0) {
                    // Check balance payment account
                    $balancePaymentAccount = ChartOfAccount::find(request()->get('payment_account_id'))->total(date('Y-m-d 23:59:59'));
                    if ($balancePaymentAccount < $needToPayFromAccount) {
                        throw new PointException('Balance is not enough');
                    }
                }
            }
        });
    }
}
