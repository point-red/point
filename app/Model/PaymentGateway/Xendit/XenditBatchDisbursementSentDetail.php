<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditBatchDisbursementSentDetail extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_batch_disbursement_sent_details';

    public function xenditBatchDisbursementSent() {
        return $this->belongsTo(XenditBatchDisbursementSent::class, 'xendit_batch_disbursement_sent_id');
    }
}
