<?php

namespace App\Model\Finance\PaymentOrder;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\TransactionModel;

class PaymentOrderDetail extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'payment_order_detail';

    public $timestamps = false;

    protected $fillable = [
        'chart_of_account_id',
        'allocation_id',
        'amount',
        'notes',
        'referenceable_type',
        'referenceable_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    /**
     * Get all of the owning referenceable models.
     */
    public function referenceable()
    {
        return $this->morphTo();
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
