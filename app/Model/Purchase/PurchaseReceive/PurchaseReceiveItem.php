<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceiveItem extends Model
{
    protected $connection = 'tenant';
    
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'quantity',
        'unit',
        'converter',
    ];

    protected $casts = [
        'quantity'  => 'double',
        'converter' => 'double',
    ];

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
