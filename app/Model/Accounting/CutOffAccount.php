<?php

namespace App\Model\Accounting;

use App\Model\PointModel;
use App\Traits\Model\Accounting\CutOffAccountJoin;

class CutOffAccount extends PointModel
{
    use CutOffAccountJoin;

    protected $connection = 'tenant';

    public static $alias = 'cutoff_accounts';

    protected $table = 'cutoff_accounts';

    protected $casts = [
        'debit' => 'double',
        'credit' => 'double',
    ];

    /**
     * Get the account that owns the cut off account.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    /**
     * Get the account that owns the cut off account.
     */
    public function cutoff()
    {
        return $this->belongsTo(CutOff::class, 'cutoff_id');
    }

    /**
     * Get the account that owns the cut off account.
     */
    public function cutOffDetails()
    {
        return $this->hasMany(CutOffDetail::class, 'cutoff_account_id');
    }
}
