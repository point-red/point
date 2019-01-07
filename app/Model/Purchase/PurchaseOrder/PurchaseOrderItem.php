<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Master\Item;
use App\Model\Master\Allocation;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_item_id',
        'item_id',
        'quantity',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'unit',
        'converter',
        'description',
        'allocation_id',
    ];

    protected $casts = [
        'quantity'         => 'double',
        'price'            => 'double',
        'discount_percent' => 'double',
        'discount_value'   => 'double',
        'converter'        => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
