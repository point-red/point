<?php

namespace App\Model\Sales\SalesOrder;

use App\Model\Form;
use App\Model\Master\Allocation;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\SalesOrderJoin;
use App\Traits\Model\Sales\SalesOrderRelation;
use Carbon\Carbon;

class SalesOrder extends TransactionModel
{
    use SalesOrderRelation, SalesOrderJoin;

    public static $morphName = 'SalesOrder';

    protected $connection = 'tenant';

    public static $alias = 'sales_order';

    public $timestamps = false;

    public $defaultNumberPrefix = 'SO';

    protected $fillable = [
        'sales_quotation_id',
        'sales_contract_id',
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'warehouse_id',
        'eta',
        'cash_only',
        'need_down_payment',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
        'billing_address',
        'billing_phone',
        'billing_email',
    ];

    protected $casts = [
        'amount' => 'double',
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
        'need_down_payment' => 'double',
    ];

    public function getEtaAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setEtaAttribute($value)
    {
        $this->attributes['eta'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function isComplete()
    {
        if ($this->items->count() === 0) {
            return false;
        }

        $complete = true;
        foreach ($this->items as $item) {
            foreach ($item->deliveryOrderItems as $orderItem) {                
                if ($orderItem->deliveryOrder->form->cancellation_status == null
                    || $orderItem->deliveryOrder->form->cancellation_status !== 1
                    || $orderItem->deliveryOrder->form->number !== null) {
                        $quantityOrdered = $item->deliveryOrderItems->sum('quantity');
                        if ($item->quantity > $quantityOrdered) {
                            $complete = false;
                            break;
                        }
                }
            }
        }

        return $complete;
    }

    public function updateStatus()
    {
        if ($this->isComplete()) {
            $this->form->done = true;
            $this->form->save();
        } else {
            $this->form->done = false;
            $this->form->save();
        }
    }

    public function isAllowedToUpdate()
    {
//        $this->updatedFormNotArchived();
//        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
//        $this->updatedFormNotArchived();
//        $this->isNotReferenced();
    }

    public static function create($data)
    {
        $salesOrder = new self;
        $salesOrder->fill($data);

        $items = self::mapItems($data['items'] ?? []);

        $salesOrder->amount = self::calculateAmount($salesOrder, $items);
        $salesOrder->save();

        $salesOrder->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $salesOrder);

        self::setReferenceDone($salesOrder);

        return $salesOrder;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $salesOrderItem = new SalesOrderItem;
            $salesOrderItem->fill($item);

            return $salesOrderItem;
        }, $items);
    }

    private static function calculateAmount($salesOrder, $items)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + ($item->price - $item->discount_value) * $item->quantity * $item->converter;
        }, 0);

        $amount -= $salesOrder->discount_value;
        $amount += $salesOrder->delivery_fee;
        $amount += $salesOrder->type_of_tax === 'exclude' ? $salesOrder->tax : 0;

        return $amount;
    }

    private static function setReferenceDone($salesOrder)
    {
        if (! is_null($salesOrder->sales_contract_id)) {
            $salesOrder->salesContract->updateStatus();
        } elseif (! is_null($salesOrder->sales_quotation_id)) {
            $salesOrder->salesQuotation->updateStatus();
        }
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
//        if ($this->deliveryOrders->count()) {
//            throw new IsReferencedException('Cannot edit form because referenced by delivery order(s)', $this->deliveryOrders);
//        }
//        if ($this->downPayments->count()) {
//            throw new IsReferencedException('Cannot edit form because referenced by down payment(s)', $this->downPayments);
//        }
    }
}
