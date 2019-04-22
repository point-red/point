<?php

namespace App\Model\Purchase\PurchaseContract;

use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;

class PurchaseContract extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'CONTRACT/P/';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function groupItems()
    {
        return $this->hasMany(PurchaseContractGroupItem::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseContractItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)->active();
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by purchase order
        if ($this->purchaseOrders->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase order', $this->purchaseOrders);
        }
    }

    public static function create($data)
    {
        $purchaseContract = new self;
        $purchaseContract->fill($data);

        if (! empty($data['items'])) {
            $purchaseContract = self::createContractItem($purchaseContract, $data['items']);
        } elseif (! empty($data['group_items'])) {
            $purchaseContract = self::createContractGroupItems($purchaseContract, $data['group_items']);
        }

        $form = new Form;
        $form->saveData($data, $purchaseContract);

        return $purchaseContract;
    }

    /**
     * Separate function for contract item because
     * contract can has only items or item groups
     * and not both at the same contract.
     */
    private static function createContractItem($purchaseContract, $items)
    {
        $items = self::getItems($items);
        $purchaseContract->amount = self::getAmount($items);
        $purchaseContract->save();
        $purchaseContract->items()->saveMany($items);

        return $purchaseContract;
    }

    private static function createContractGroupItems($purchaseContract, $groupItems)
    {
        $groupItems = self::getGroupItems($groupItems);
        $purchaseContract->amount = self::getAmount($groupItems);
        $purchaseContract->save();
        $purchaseContract->groupItems()->saveMany($groupItems);

        return $purchaseContract;
    }

    private static function getItems($items)
    {
        return array_map(function ($item) {
            $contractItem = new PurchaseContractItem;
            $contractItem->fill($item);

            return $contractItem;
        }, $items);
    }

    private static function getGroupItems($groupItems)
    {
        return array_map(function ($groupItem) {
            $contractGroupItem = new PurchaseContractGroupItem;
            $contractGroupItem->fill($groupItem);

            return $contractGroupItem;
        }, $groupItems);
    }

    private static function getAmount($details)
    {
        return array_reduce($details, function ($carry, $detail) {
            return $carry + $detail->quantity * $detail->price;
        }, 0);
    }
}
