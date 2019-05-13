<?php

namespace App\Model\Sales\SalesQuotation;

use Illuminate\Database\Eloquent\Model;

class SalesQuotationService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'quantity',
        'price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
    ];
}
