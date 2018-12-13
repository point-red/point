<?php

namespace App\Model\Purchase\PurchaseRequest;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'quantity',
        'unit',
        'converter',
        'price',
        'description',
    ];

    protected $casts = [
        'quantity'  => 'double',
        'price'     => 'double',
        'converter' => 'double',
    ];
}
