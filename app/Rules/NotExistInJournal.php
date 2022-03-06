<?php

namespace App\Rules;

use App\Model\Accounting\Journal;
use Illuminate\Contracts\Validation\Rule;

class NotExistInJournal implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !Journal::select('id')->where('chart_of_account_id', $value)->first();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Account already have balance';
    }
}
