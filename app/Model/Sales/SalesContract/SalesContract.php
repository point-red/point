<?php

namespace App\Model\Sales\SalesContract;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\TransactionModel;

class SalesContract extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'CONTRACT';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function groupItems()
    {
        return $this->hasMany(SalesContractGroupItem::class);
    }

    public function items()
    {
        return $this->hasMany(SalesContractItem::class);
    }

    public static function create($data)
    {
        $salesContract = new self;
        if (empty($data['customer_name'])) {
            $data['customer_name'] = Customer::find($data['customer_id'], ['name']);
        }
        $salesContract->fill($data);
        $salesContract->save();

        $form = new Form;
        $form->fillData($data, $salesContract);

        $items = [];
        $groupItems = [];
        $amount = 0;

        if ($data['items']) {
            foreach ($data['items'] as $item) {
                $contractItem = new SalesContractItem;
                $contractItem->fill($item);

                $amount += $item->quantity * $item->price;

                array_push($items, $contractItem);
            }
        } elseif ($data['group_items']) {
            foreach ($data['group_items'] as $groupItem) {
                $contractGroupItem = new SalesContractGroupItem;
                $contractGroupItem->fill($groupItem);

                $amount += $groupItem->quantity * $groupItem->price;

                array_push($groupItems, $contractGroupItem);
            }
        }

        $salesContract->amount = $amount;
        $salesContract->save();

        if (! empty($items)) {
            $salesContract->items()->saveMany($items);
        }

        if (! empty($groupItems)) {
            $salesContract->groupItems()->saveMany($items);
        }

        return $salesContract;
    }
}
