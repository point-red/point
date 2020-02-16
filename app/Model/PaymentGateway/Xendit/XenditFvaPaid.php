<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditFvaPaid extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_fva_paid';

    protected $fillable = [
        'external_id',
        'owner_id',
        'payment_id',
        'bank_code',
        'account_number',
        'callback_virtual_account_id',
        'merchant_code',
        'transaction_timestamp',
        'amount',
        'created',
        'updated',
    ];

    protected $casts = [
        'amount' => 'double',
    ];
}
