<?php

namespace App\Model\Inventory\OpeningStock;

use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class OpeningStockWarehouse extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'opening_stock_warehouse';

    public $timestamps = false;

    protected $fillable = [
        'expiry_date',
        'production_number',
    ];

    protected $casts = [
        'price' => 'double',
        'quantity' => 'double',
    ];

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = convert_to_server_timezone($value);
    }

    public function getExpiryDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function openingStock()
    {
        return $this->belongsTo(OpeningStock::class);
    }
}
