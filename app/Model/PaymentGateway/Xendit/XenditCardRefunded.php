<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditCardRefunded extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_card_refunded';

    protected $fillable = [
        'xendit_id',
        'external_id',
        'user_id',
        'credit_card_charge_id',
        'status',
        'amount',
        'fee_refund_amount',
        'created',
        'updated',
    ];

    protected $casts = [
        'amount' => 'double',
        'fee_refund_amount' => 'double',
    ];
}
