<?php

namespace App\Model\Accounting;

use App\Model\MasterModel;

class ChartOfAccountGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_account_groups';

    public static $alias = 'account_group';

    public static $morphName = 'ChartOfAccountGroup';
}
