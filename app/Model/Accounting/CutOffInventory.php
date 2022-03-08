<?php

namespace App\Model\Accounting;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\PointModel;

class CutOffInventory extends PointModel
{
    protected $connection = 'tenant';

    public static $alias = 'cutoff_inventory';

    protected $table = 'cutoff_inventories';

    public static $morphName = 'CutoffInventory';

    protected $fillable = [
        'warehouse_id',
        'quantity',
        'unit',
        'converter',
        'price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'total' => 'double',
    ];

    /**
     * Get the item that owns the cut off account.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function dna()
    {
        return $this->hasMany(CutOffInventoryDna::class, 'item_id',  'item_id');
    }

    /**
     * Get the warehouse that owns the cut off account.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get all of the item's journals.
     */
    public function cutoffDetail()
    {
        return $this->morphMany(CutOffDetail::class, 'cutoffable');
    }
}
