<?php

namespace App\Model\Accounting;

use App\Model\Master\Supplier;
use App\Model\PointModel;

class CutOffAccountPayable extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_account_payables';

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
     * Get the supplier that owns the cut off account.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the account that owns the cut off account.
     */
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
