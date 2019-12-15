<?php

namespace App\Model\Sales\SalesContract;

use App\Model\Master\ItemGroup;
use App\Model\Sales\SalesOrder\SalesOrderItem;
use App\Model\TransactionModel;

class SalesContractGroupItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_group_id',
        'price',
        'quantity',
        'discount_percent',
        'discount_value',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'price' => 'double',
        'quantity' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class);
    }

    public function group()
    {
        return $this->belongsTo(ItemGroup::class);
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class)
            ->whereHas('salesOrder', function ($query) {
                $query->active();
            });
    }
}
