<?php

namespace App\Model\Purchase\PurchaseContract;

use App\Model\Master\ItemGroup;
use App\Model\TransactionModel;

class PurchaseContractGroupItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'purchase_contract_group';

    public $timestamps = false;

    protected $fillable = [
        'item_group_id',
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
        return $this->belongsTo(ItemGroup::class);
    }
}
