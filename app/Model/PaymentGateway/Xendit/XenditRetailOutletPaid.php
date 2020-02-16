<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditRetailOutletPaid extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_retail_outlet_paid';

    protected $fillable = [
        'external_id',
        'user_id',
        'prefix',
        'retail_outlet_name',
        'name',
        'amount',
        'fees_paid_amount',
        'payment_id',
        'payment_code',
        'fixed_payment_code_payment_id',
        'fixed_payment_code_id',
        'status',
        'transaction_id',
        'transaction_timestamp',
        'created',
        'updated',
    ];

    protected $casts = [
        'amount' => 'double',
        'fees_paid_amount' => 'double',
    ];
}
