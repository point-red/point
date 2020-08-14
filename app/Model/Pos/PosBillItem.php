<?php

namespace App\Model\Pos;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class PosBillItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'pos_bill_item';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'quantity',
        'expiry_date',
        'production_number',
        'unit',
        'converter',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = convert_to_server_timezone($value);
    }

    public function getExpiryDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function posBill()
    {
        return $this->belongsTo(PosBill::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
