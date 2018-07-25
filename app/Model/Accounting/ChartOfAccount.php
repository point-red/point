<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_accounts';

    /**
     * Get the type that owns the chart of account.
     */
    public function type()
    {
        return $this->belongsTo(get_class(new ChartOfAccountType()), 'type_id');
    }

    /**
     * Get the type that owns the chart of account.
     */
    public function group()
    {
        return $this->belongsTo(get_class(new ChartOfAccountGroup()), 'group_id');
    }
}
