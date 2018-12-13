<?php

namespace App\Model\Purchase\PurchaseRequest;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'quantity',
        'unit',
        'converter',
        'description',
    ];
}
