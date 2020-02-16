<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditFvaCreated extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_fva_created';

    protected $fillable = [
        'external_id',
        'owner_id',
        'bank_code',
        'account_number',
        'merchant_code',
        'name',
        'status',
        'is_closed',
        'is_single_use',
        'expiration_date',
        'created',
        'updated',
    ];
}
