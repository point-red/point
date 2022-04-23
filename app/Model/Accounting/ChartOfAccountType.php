<?php

namespace App\Model\Accounting;

use App\Model\MasterModel;
use App\Traits\Model\Accounting\ChartOfAccountTypeJoin;
use App\Traits\Model\Accounting\ChartOfAccountTypeRelation;

/**
 * @property int $id
 */
class ChartOfAccountType extends MasterModel
{
    use ChartOfAccountTypeJoin, ChartOfAccountTypeRelation;

    protected $connection = 'tenant';

    protected $table = 'chart_of_account_types';

    public static $alias = 'account_type';

    public static $morphName = 'ChartOfAccountType';

    public function totalDebit()
    {
        return $this->accounts->totalDebit();
    }

    public function totalCredit()
    {
        return $this->accounts->totalCredit();
    }

    public function total()
    {
        if ($this->is_debit) {
            return $this->totalDebit() - $this->totalCredit();
        }

        return $this->totalCredit() - $this->totalDebit();
    }
}
