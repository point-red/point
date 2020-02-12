<?php

namespace App\Model\Accounting;

use App\Model\Master\Customer;
use App\Model\PointModel;

class CutOffAccountReceivable extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_account_receivables';

    protected $casts = [
        'amount' => 'double',
    ];

    /**
     * Get the cut off that owns the cut off account.
     */
    public function cutOff()
    {
        return $this->belongsTo(CutOff::class, 'cut_off_id');
    }

    /**
     * Get the customer that owns the cut off account.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the account that owns the cut off account.
     */
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
