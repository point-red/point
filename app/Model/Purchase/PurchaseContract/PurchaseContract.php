<?php

namespace App\Model\Purchase\PurchaseContract;

use App\Model\Master\Supplier;
use App\Model\TransactionModel;

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

    public static function create($data)
    {
        $purchaseContract = new self;
        if (empty($data['supplier_name'])) {
            $data['supplier_name'] = Supplier::find($data['supplier_id'], ['name']);
        }
        $purchaseContract->fill($data);
        $purchaseContract->save();

        $form = new Form;
        $form->fillData($data, $purchaseContract);

        $items = [];
        $groupItems = [];
        $amount = 0;

        if ($data['items']) {
            foreach ($data['items'] as $item) {
                $contractItem = new PurchaseContractItem;
                $contractItem->fill($item);

                $amount += $item->quantity * $item->price;

                array_push($items, $contractItem);
            }
        } elseif ($data['group_items']) {
            foreach ($data['group_items'] as $groupItem) {
                $contractGroupItem = new PurchaseContractGroupItem;
                $contractGroupItem->fill($groupItem]);

                $amount += $groupItem->quantity * $groupItem->price;

                array_push($groupItems, $contractGroupItem);
            }
        }

        $purchaseContract->amount = $amount;
        $purchaseContract->save();

        if (! empty($items)) {
            $purchaseContract->items()->saveMany($items);
        }

        if (! empty($groupItems)) {
            $purchaseContract->groupItems()->saveMany($items);
        }

        return $purchaseContract;
    }
}
