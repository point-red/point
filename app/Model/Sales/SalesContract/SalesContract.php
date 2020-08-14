<?php

namespace App\Model\Sales\SalesContract;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesContract extends TransactionModel
{
    public static $morphName = 'SalesContract';

    protected $connection = 'tenant';

    public static $alias = 'sales_contract';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'cash_only',
        'need_down_payment',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

    protected $casts = [
        'amount' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
        'need_down_payment' => 'double',
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
        return $this->hasMany(SalesOrder::class)->active();
    }

    public function downPayments()
    {
        return $this->morphMany(SalesDownPayment::class, 'downpaymentable')->active();
    }

    public function paidDownPayments()
    {
        return $this->downPayments()->whereNotNull('paid_by');
    }

    public function remainingDownPayments()
    {
        return $this->paidDownPayments()->where('remaining', '>', 0);
    }

    public function updateStatus()
    {
        // Make form done when all items / group items quantity ordered
        $done = true;

        if ($this->items->isNotEmpty()) {
            $items = $this->items()->with('salesOrderItems')->get();

            foreach ($items as $item) {
                $quantityOrdered = $item->salesOrderItems->sum('quantity');
                if ($item->quantity - $quantityOrdered > 0) {
                    $done = false;
                    break;
                }
            }
        } elseif ($this->groupItems->isNotEmpty()) {
            $groupItems = $this->groupItems()->with('salesOrderItems')->get();

            foreach ($groupItems as $groupItem) {
                $quantityOrdered = $groupItem->salesOrderItems->sum('quantity');
                if ($groupItem->quantity - $quantityOrdered > 0) {
                    $done = false;
                    break;
                }
            }
        }

        $this->form()->update(['done' => $done]);
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public static function create($data)
    {
        $salesContract = new self;
        $salesContract->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $groupItems = self::mapGroupItems($data['groups'] ?? []);

        $salesContract->amount = self::calculateAmount($salesContract, $items, $groupItems);
        $salesContract->save();

        $salesContract->items()->saveMany($items);
        $salesContract->groupItems()->saveMany($groupItems);

        $form = new Form;
        $form->saveData($data, $salesContract);

        return $salesContract;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $contractItem = new SalesContractItem;
            $contractItem->fill($item);

            return $contractItem;
        }, $items);
    }

    private static function mapGroupItems($groups)
    {
        return array_map(function ($group) {
            $contractGroup = new SalesContractGroupItem;
            $contractGroup->fill($group);

            return $contractGroup;
        }, $groups);
    }

    private static function calculateAmount($salesContract, $items, $groups)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * $item->converter * ($item->price - $item->discount_value);
        }, 0);

        $amount += array_reduce($groups, function ($carry, $group) {
            return $carry + $group->quantity * ($group->price - $group->discount_value);
        }, 0);

        $amount -= $salesContract->discount_value;
        $amount += $salesContract->type_of_tax === 'exclude' ? $salesContract->tax : 0;

        return $amount;
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->salesOrders->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by sales order', $this->salesOrders);
        }
    }
}
