<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Form;
use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
use App\Model\TransactionModel;

class PurchaseRequestItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'purchase_request_item';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'converter',
        'price',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'converter' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class)
            ->whereHas('purchaseOrder', function ($query) {
                $query->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PurchaseOrder::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PurchaseOrder::$morphName);
                })->whereNotNull(Form::getTableName('number'))
                    ->where(function ($q) {
                        $q->whereNull(Form::getTableName('cancellation_status'))
                            ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
                    });
            });
    }
}
