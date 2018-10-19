<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $connection = 'tenant';

    protected $table = 'journals';

    public function chartOfAccount()
    {
        return $this->belongsTo(get_class(new ChartOfAccount()), 'chart_of_account_id');
    }

    public function scopeHasValue($query)
    {
        $query->where(function ($q) {
            $q->where('debit', '!=', 0)->orWhere('credit', '!=', 0);
        });
    }
}
