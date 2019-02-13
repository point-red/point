<?php

namespace App\Model\Accounting;

use App\Model\MasterModel;

class ChartOfAccountGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_account_groups';
}
