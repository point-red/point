<?php

namespace App\Model\Sales\SalesContract;

use App\Model\TransactionModel;

class SalesContractItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_unit_id',
        'item_name',
        'price',
        'quantity',
        'unit',
        'converter',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'price' => 'double',
        'quantity' => 'double',
        'converter' => 'double',
    ];

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class);
    }
}
