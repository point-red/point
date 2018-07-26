<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccountType extends Model
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_account_types';

    public function accounts()
    {
        return $this->hasMany(get_class(new ChartOfAccount()), 'type_id');
    }

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
