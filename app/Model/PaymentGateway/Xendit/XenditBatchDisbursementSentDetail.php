<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditBatchDisbursementSentDetail extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_batch_disbursement_sent_details';

    protected $fillable = [
        'external_id',
        'amount',
        'valid_name',
        'description',
        'status',
        'bank_code',
        'bank_reference',
        'bank_account_number',
        'bank_account_name',
        'created',
        'updated',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function xenditBatchDisbursementSent()
    {
        return $this->belongsTo(XenditBatchDisbursementSent::class, 'xendit_batch_disbursement_sent_id');
    }
}
