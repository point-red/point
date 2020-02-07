<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccountSubLedger extends Model
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_account_sub_ledgers';

}
