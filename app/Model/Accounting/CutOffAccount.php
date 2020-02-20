<?php

namespace App\Model\Accounting;

use App\Model\PointModel;

class CutOffAccount extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_accounts';

    protected $casts = [
        'debit' => 'double',
        'credit' => 'double',
    ];

    /**
     * Get the cut off that owns the cut off account.
     */
    public function cutOff()
    {
        return $this->belongsTo(CutOff::class, 'cut_off_id');
    }

    /**
     * Get the account that owns the cut off account.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
