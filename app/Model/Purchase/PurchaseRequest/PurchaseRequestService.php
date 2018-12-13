<?php

namespace App\Model\Purchase\PurchaseRequest;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id',
        'service_id',
        'quantity',
        'price',
        'description',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price'    => 'double',
    ];
}
