<?php

namespace App\Model\Accounting;

use App\Model\PointModel;

class ChartOfAccountSubLedger extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_account_sub_ledgers';

}
