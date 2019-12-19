<?php

namespace App\Model\Inventory\OpeningStock;

use App\Model\TransactionModel;

class OpeningStockWarehouse extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'production_number',
        'expiry_date',
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

    public function openingStock()
    {
        return $this->belongsTo(OpeningStock::class);
    }
}
