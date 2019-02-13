<?php

namespace App\Helpers\Accounting;

use App\Model\Accounting\ChartOfAccountType;

class Account
{
    public static function currentLiabilities()
    {
        return optional(ChartOfAccountType::where('name', 'current liability')->first())->accounts;
    }

    public static function accountReceivables()
    {
        return optional(ChartOfAccountType::where('name', 'account receivable')->first())->accounts;
    }
}
