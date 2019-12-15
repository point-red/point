<?php

namespace App\Model\Pos;

use App\Model\Master\Service;
use App\Model\TransactionModel;

class PosBillService extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'service_name',
        'quantity',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function posBill()
    {
        return $this->belongsTo(PosBill::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
