<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditInvoicePaid extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_invoice_paid';

    protected $fillable = [
        'external_id',
        'user_id',
        'is_high',
        'status',
        'merchant_name',
        'payer_email',
        'description',
        'bank_code',
        'payment_method',
        'payment_channel',
        'payment_destination',
        'currency',
        'amount',
        'paid_amount',
        'adjusted_received_amount',
        'fees_paid_amount',
        'paid_at',
        'created',
        'updated',
    ];

    protected $casts = [
        'amount' => 'double',
        'paid_amount' => 'double',
        'adjusted_received_amount' => 'double',
        'fees_paid_amount' => 'double',
    ];
}
