<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Form;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;
use App\Model\Sales\SalesReturn\SalesReturnItem;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\TransactionModel;

class SalesInvoiceItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'sales_invoice_item';

    public $timestamps = false;

    protected $fillable = [
        'delivery_note_id',
        'delivery_note_item_id',
        'referenceable_id',
        'referenceable_type',
        'item_referenceable_id',
        'item_referenceable_type',
        'item_id',
        'item_name',
        'quantity',
        'quantity_returned',
        'quantity_remaining',
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
        'quantity_returned' => 'double',
        'quantity_remaining' => 'double',
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

    public function salesReturnItems()
    {
        return $this->hasMany(SalesReturnItem::class)
            ->whereHas('salesReturn', function ($query) {
                $query->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', SalesReturn::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), SalesReturn::$morphName);
                })->whereNotNull(Form::getTableName('number'))
                    ->where(function ($q) {
                        $q->whereNull(Form::getTableName('cancellation_status'))
                            ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
                    });
            });
    }

    public function salesReturnItemReturned()
    {
        return $this->salesReturnItems()
            ->whereHas('salesReturn.form', function ($query) {
                $query->where('approval_status', 1);
            })
            ->get()
            ->sum('quantity');
    }

    public function convertUnitToSmallest()
    {
        $smallestItemUnit = $this->item->smallest_unit;
        
        if($this->attributes['unit'] !== $smallestItemUnit->label) {
            $this->attributes['unit_smallest'] = $smallestItemUnit->label;
            $this->attributes['converter_smallest'] = $smallestItemUnit->converter;
            
            $this->attributes['quantity_remaining'] = $this->attributes['quantity'] * $this->attributes['converter'];
            return;
        }

        $this->attributes['quantity_remaining'] = $this->attributes['quantity'];
    }
}
