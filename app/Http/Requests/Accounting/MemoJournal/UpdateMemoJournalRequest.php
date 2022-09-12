<?php

namespace App\Http\Requests\Accounting\MemoJournal;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemoJournalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! tenant(auth()->user()->id)->hasPermissionTo('update memo journal')) {
            return false;
        }
        
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

        $rulesMemoJournal = [
            'items' => 'required_without:services|array',
        ];

        $rulesMemoJournalItems = [
            'items.*.chart_of_account_id' => ValidationRule::foreignKey('chart_of_accounts'),
            'items.*.chart_of_account_name' => 'required|string',
            'items.*.debit' => ValidationRule::price(),
            'items.*.credit' => ValidationRule::price(),
        ];

        return array_merge($rulesForm, $rulesMemoJournal, $rulesMemoJournalItems);
    }
}
