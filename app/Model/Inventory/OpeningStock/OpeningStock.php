<?php

namespace App\Model\Inventory\OpeningStock;

use App\Model\TransactionModel;

class OpeningStock extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    public $defaultNumberPrefix = 'OS';

    public function stockWarehouse()
    {
        return $this->hasMany(OpeningStockWarehouse::class, 'opening_stock_warehouse_id');
    }
}
