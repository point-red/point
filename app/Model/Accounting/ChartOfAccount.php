<?php

namespace App\Model\Accounting;

use App\Model\MasterModel;
use App\Traits\Model\Accounting\ChartOfAccountJoin;
use App\Traits\Model\Accounting\ChartOfAccountRelation;

class ChartOfAccount extends MasterModel
{
    use ChartOfAccountJoin, ChartOfAccountRelation;

    protected $connection = 'tenant';

    protected $table = 'chart_of_accounts';

    protected $appends = ['label'];

    public static $alias = 'account';

    public static $morphName = 'ChartOfAccount';

    public function getLabelAttribute()
    {
        $label = $this->number ? '[' . $this->number . '] ' : '';

        return $label . $this->alias;
    }

    public function totalDebit($date)
    {
        return $this->journals($date)->sum('debit');
    }

    public function totalCredit($date)
    {
        return $this->journals($date)->sum('credit');
    }

    public function total($date)
    {
        if ($this->type->is_debit) {
            return $this->totalDebit($date) - $this->totalCredit($date);
        }

        return $this->totalCredit($date) - $this->totalDebit($date);
    }
}
