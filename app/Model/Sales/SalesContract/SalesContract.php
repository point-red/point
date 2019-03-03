<?php

namespace App\Model\Sales\SalesContract;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Group;
use App\Model\Master\Item;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesContract extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
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

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public static function create($data)
    {
        $salesContract = new self;
        if (empty($data['customer_name'])) {
            $data['customer_name'] = Customer::find($data['customer_id'], ['name']);
        }
        $salesContract->fill($data);

        $items = [];
        $groupItems = [];
        $amount = 0;

        if (!empty($data['items'])) {
            $itemIds = array_column($data['items'], 'item_id');
            $dbItems = Item::select('id', 'name')->whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($data['items'] as $item) {
                $contractItem = new SalesContractItem;
                $contractItem->fill($item);
                $contractItem->item_name = $dbItems[$item['item_id']]->name;

                $amount += $item['quantity'] * $item['price'];

                array_push($items, $contractItem);
            }
        } else if (!empty($data['groups'])) {
            $groupIds = array_column($data['groups'], 'group_id');
            $dbGroups = Group::select('id', 'name')->whereIn('id', $groupIds)->get()->keyBy('id');

            foreach ($data['groups'] as $groupItem) {
                $contractGroup = new SalesContractGroupItem;
                $contractGroup->fill($groupItem);
                $contractGroup->group_name = $dbGroups[$groupItem['group_id']]->name;

                $amount += $groupItem['quantity'] * $groupItem['price'];

                array_push($groupItems, $contractGroup);
            }
        }

        $salesContract->amount = $amount;
        $salesContract->save();

        if (!empty($items)) {
            $salesContract->items()->saveMany($items);
        } else if (!empty($groupItems)) {
            $salesContract->groupItems()->saveMany($groupItems);
        }

        $form = new Form;
        $form->fillData($data, $salesContract);

        return $salesContract;
    }
}
