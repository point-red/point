<?php

namespace App\Model\Inventory;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\PointModel;
use App\Traits\FormScopes;

class Inventory extends PointModel
{
    use FormScopes;

    protected $connection = 'tenant';

    protected $casts = [
        'price' => 'double',
        'quantity' => 'double',
        'cogs' => 'double',
        'total_quantity' => 'double',
        'total_value' => 'double',
    ];

    /**
     * The form that belong to the inventory.
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /**
     * The warehouse that belong to the inventory.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * The item that belong to the inventory.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * The item unit that belong to the inventory.
     */
    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class);
    }
}
