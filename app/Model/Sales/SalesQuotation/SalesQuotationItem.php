<?php

namespace App\Model\Sales\SalesQuotation;

use App\Model\Sales\SalesOrder\SalesOrderItem;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationItem extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'quantity',
        'unit',
        'converter',
        'price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'converter' => 'double',
    ];

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class)
            ->whereHas('salesOrder', function ($query) {
                $query->active();
            });
    }
}
