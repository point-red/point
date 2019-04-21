<?php

namespace App\Model\Sales\SalesOrder;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Service;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;

class SalesOrder extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    public $defaultNumberPrefix = 'SO';

    protected $fillable = [
        'sales_quotation_id',
        'sales_contract_id',
        'customer_id',
        'customer_name',
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

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function services()
    {
        return $this->hasMany(SalesOrderService::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class)->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function downPayments()
    {
        return $this->morphMany(SalesDownPayment::class, 'downpaymentable')->active();
    }

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class);
    }

    public function updateIfDone()
    {
        $salesOrderItems = $this->items;
        $salesOrderItemIds = $salesOrderItems->pluck('id');

        $tempArray = DeliveryOrder::active()
            ->join(DeliveryOrderItem::getTableName(), DeliveryOrder::getTableName('id'), '=', DeliveryOrderItem::getTableName('delivery_order_id'))
            ->groupBy('sales_order_item_id')
            ->select(DeliveryOrderItem::getTableName('sales_order_item_id'))
            ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
            ->whereIn('sales_order_item_id', $salesOrderItemIds)
            ->get();

        $quantityDeliveredItems = $tempArray->pluck('sum_delivered', 'sales_order_item_id');

        // Make form done when all item delivered
        $done = true;
        foreach ($salesOrderItems as $salesOrderItem) {
            $quantityDelivered = $quantityDeliveredItems[$salesOrderItem->id] ?? 0;
            if ($salesOrderItem->quantity - $quantityDelivered > 0) {
                $done = false;
                break;
            }
        }

        if ($done == true) {
            $this->form->done = true;
            $this->form->save();
        }
    }
    
    public function isAllowedToUpdate($date)
    {
        $this->updatedFormInSamePeriod($date);
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
        $salesOrder = new self;
        $salesOrder->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $salesOrder->amount = self::calculateAmount($salesOrder, $items, $services);
        $salesOrder->save();

        $salesOrder->items()->saveMany($items);
        $salesOrder->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $salesOrder);

        self::setReferenceDone($salesOrder);

        return $salesOrder;
    }

    private static function mapItems($items)
    {
        return array_map(function($item) {
            $salesOrderItem = new SalesOrderItem;
            $salesOrderItem->fill($item);

            return $salesOrderItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function($service) {
            $salesOrderService = new SalesOrderService;
            $salesOrderService->fill($service);

            return $salesOrderService;
        }, $services);
    }

    private static function calculateAmount($salesOrder, $items, $services)
    {
        $amount = array_reduce($items, function($carry, $item) {
            return $carry + ($item->price - $item->discount_value) * $item->quantity * $item->converter;
        }, 0);

        $amount += array_reduce($services, function($carry, $service) {
            return $carry + ($service->price - $service->discount_value) * $service->quantity;
        }, 0);

        $amount -= $salesOrder->discount_value;
        $amount += $salesOrder->delivery_fee;
        $amount += $salesOrder->type_of_tax === 'exclude' ? $salesOrder->tax : 0;

        return $amount;
    }

    private static function setReferenceDone($salesOrder)
    {
        if (! is_null($salesOrder->sales_contract_id)) {
            $salesOrder->salesContract->updateIfDone();
        } else if (! is_null($salesOrder->sales_quotation_id)) {
            $salesOrder->salesQuotation->updateIfDone();
        }
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->deliveryOrders->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by delivery order(s)', $this->deliveryOrders);
        }
        if ($this->downPayments->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by down payment(s)', $this->downPayments);
        }
    }
}
