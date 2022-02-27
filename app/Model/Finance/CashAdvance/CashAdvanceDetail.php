<?php

namespace App\Model\Finance\CashAdvance;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\TransactionModel;

class CashAdvanceDetail extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'cash_advance_detail';

    public $timestamps = false;

    protected $fillable = [
        'chart_of_account_id',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
