<?php

namespace App\Model\Inventory;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\PointModel;

class Inventory extends PointModel
{
    protected $connection = 'tenant';

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
}
