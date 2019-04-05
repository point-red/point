<?php

namespace App\Model\Purchase\PurchaseContract;

use App\Model\TransactionModel;

class PurchaseContractGroupItem extends TransactionModel
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

    public function purchaseContract()
    {
        return $this->belongsTo(PurchaseContract::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
