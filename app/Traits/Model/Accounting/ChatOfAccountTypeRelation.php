<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\ChartOfAccount;

trait ChatOfAccountTypeRelation
{
    public function accounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'type_id');
    }
}
