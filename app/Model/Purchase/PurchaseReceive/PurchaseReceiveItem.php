<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\TransactionModel;

class PurchaseReceiveItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_order_item_id',
        'item_id',
        'item_name',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'quantity',
        'expiry_date',
        'production_number',
        'unit',
        'converter',
        'notes',
        'allocation_id',
        'price',
        'purchase_price',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'converter' => 'double',
    ];

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = convert_to_server_timezone($value);
    }

    public function getExpiryDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function purchaseReceive()
    {
        return $this->belongsTo(PurchaseReceive::class);
    }
}
