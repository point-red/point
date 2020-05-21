<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditBatchDisbursementSent extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_batch_disbursement_sent';

    protected $fillable = [
        'user_id',
        'approver_id',
        'approved_at',
        'total_disbursed_count',
        'total_disbursed_amount',
        'total_error_count',
        'total_error_amount',
        'total_upload_count',
        'total_upload_amount',
        'reference',
        'status',
        'created',
        'updated',
    ];

    protected $casts = [
        'total_disbursed_amount' => 'double',
        'total_error_amount' => 'double',
        'total_upload_amount' => 'double',
    ];

    public function details()
    {
        return $this->hasMany(XenditBatchDisbursementSentDetail::class, 'xendit_batch_disbursement_sent_id');
    }
}
