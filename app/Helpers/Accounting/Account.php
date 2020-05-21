<?php

namespace App\Helpers\Accounting;

use App\Model\Accounting\ChartOfAccountType;

class Account
{
    public static function accountPayables()
    {
        return optional(ChartOfAccountType::where('name', 'ACCOUNT PAYABLE')->first())->accounts;
    }

    public static function accountReceivables()
    {
        return optional(ChartOfAccountType::where('name', 'ACCOUNT RECEIVABLE')->first())->accounts;
    }
}
