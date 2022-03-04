<?php

namespace App\Model\Accounting;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\PointModel;

class CutOffInventoryDna extends PointModel
{
    protected $connection = 'tenant';

    public static $alias = 'cutoff_inventory_dna';

    protected $table = 'cutoff_inventory_dnas';


    protected $fillable = [
        'item_id',
        'quantity',
        'expiry_date',
        'production_number',
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

}
