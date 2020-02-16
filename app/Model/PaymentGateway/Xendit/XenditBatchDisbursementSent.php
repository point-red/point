<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditBatchDisbursementSent extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_batch_disbursement_sent';

    public function details() {
        return $this->hasMany(XenditBatchDisbursementSentDetail::class, 'xendit_batch_disbursement_sent_id');
    }
}
