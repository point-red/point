<?php

namespace App\Http\Requests\Finance\CashAdvance\CashAdvance;

use App\Http\Requests\ValidationRule;
use App\Model\Accounting\ChartOfAccount;
use App\Model\HumanResource\Employee\Employee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCashAdvanceRequest extends FormRequest
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

        $rulesCashAdvanceForm = [
            'employee_id' => ValidationRule::foreignKey(Employee::getTableName()),
            'details' => 'required|array',
        ];

        $rulesCashAdvanceForm = [
            'details.*.chart_of_account_id' => ValidationRule::foreignKey(ChartOfAccount::getTableName()),
            'details.*.amount' => ValidationRule::price(),
        ];

        return array_merge($rulesForm, $rulesPayment, $rulesPaymentDetail);
    }
}
