<?php

namespace App\Model\Accounting;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\PointModel;

class CutOffInventory extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_inventories';

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
    ];

    /**
     * Get the cut off that owns the cut off account.
     */
    public function cutOff()
    {
        return $this->belongsTo(CutOff::class, 'cut_off_id');
    }

    /**
     * Get the item that owns the cut off account.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the warehouse that owns the cut off account.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
