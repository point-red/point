<?php

namespace App\Model\Sales\SalesOrder;

use App\Model\PointModel;
use App\Model\Master\Item;
use App\Model\Master\Allocation;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;

class SalesOrderItem extends PointModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'quantity',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'unit',
        'converter',
        'notes',
        'allocation_id',
        'sales_quotation_item_id',
        'sales_contract_item_id',
        'sales_contract_group_item_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'converter' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function deliveryOrderItems()
    {
        return $this->hasMany(DeliveryOrderItem::class)
            ->whereHas('deliveryOrder', function ($query) {
                $query->active();
            });
    }
}
