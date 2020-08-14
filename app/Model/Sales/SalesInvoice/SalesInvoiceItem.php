<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;
use App\Model\TransactionModel;

class SalesInvoiceItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'sales_invoice_item';

    public $timestamps = false;

    protected $fillable = [
        'delivery_note_id',
        'delivery_note_item_id',
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'converter',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function deliveryNoteItem()
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
