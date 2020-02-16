<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditDisbursementSent extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_disbursement_sent';

    protected $fillable = [
        'user_id',
        'external_id',
        'bank_code',
        'account_holder_name',
        'amount',
        'transaction_id',
        'transaction_sequence',
        'disbursement_id',
        'disbursement_description',
        'failure_code',
        'is_instant',
        'status',
        'created',
        'updated',
    ];

    protected $casts = [
        'amount' => 'double',
    ];
}
