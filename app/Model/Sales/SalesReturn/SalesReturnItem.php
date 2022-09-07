<?php

namespace App\Model\Sales\SalesReturn;

use App\Model\Master\Allocation;
use App\Model\TransactionModel;
use App\Model\Sales\SalesInvoice\SalesInvoiceItem;
use App\Model\Master\Item;

class SalesReturnItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'sales_return_item';

    protected $fillable = [
        'sales_return_id',
        'sales_invoice_item_id',
        'item_id',
        'item_name',
        'quantity_sales',
        'quantity',
        'price',
        'discount_percent',
        'discount_value',
        'unit',
        'converter',
        'expiry_date',
        'production_number',
        'notes',
        'allocation_id',
    ];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'sales_return_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity_sales' => 'double',
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'converter' => 'double',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function salesInvoiceItem()
    {
        return $this->belongsTo(SalesInvoiceItem::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
