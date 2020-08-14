<?php

namespace App\Model\Sales\SalesQuotation;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Sales\SalesOrder\SalesOrderItem;
use App\Model\TransactionModel;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'sales_quotation_item';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'quantity',
        'unit',
        'converter',
        'price',
        'discount_percent',
        'discount_value',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
