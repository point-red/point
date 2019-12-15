<?php

namespace App\Model\Inventory\OpeningStock;

use App\Model\TransactionModel;

class OpeningStockWarehouse extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $casts = [
        'price' => 'double',
        'quantity' => 'double',
    ];

    public function openingStock()
    {
        return $this->belongsTo(OpeningStock::class);
    }
}
