<?php

namespace App\Model\Sales\SalesContract;

use App\Model\TransactionModel;

class SalesContractGroupItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'group_name',
        'price',
        'quantity',
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
