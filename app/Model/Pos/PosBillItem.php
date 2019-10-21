<?php

namespace App\Model\Pos;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class PosBillItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'production_number',
        'expiry_date',
        'quantity',
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

    public function posBill()
    {
        return $this->belongsTo(PosBill::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
