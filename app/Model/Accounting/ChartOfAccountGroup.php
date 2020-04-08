<?php

namespace App\Model\Accounting;

use App\Model\MasterModel;

class ChartOfAccountGroup extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'chart_of_account_group';

    protected $table = 'chart_of_account_groups';
}
