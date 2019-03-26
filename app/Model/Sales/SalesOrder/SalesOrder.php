<?php

namespace App\Model\Sales\SalesOrder;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Service;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;
use App\Model\Sales\SalesContract\SalesContract;
use Carbon\Carbon;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;

class SalesOrder extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

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

    public $defaultNumberPrefix = 'SO';

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
        return $this->hasMany(DeliveryOrder::class)
            ->joinForm(DeliveryOrder::class)
            ->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function downPayments() {
        return $this->morphMany(SalesDownPayment::class, 'downpaymentable')
            ->joinForm(SalesDownPayment::class)
            ->active();
    }

    public function updateIfDone()
    {
        $salesOrderItems = $this->items;
        $salesOrderItemIds = $salesOrderItems->pluck('id');

        $tempArray = DeliveryOrder::joinForm()
            ->join(DeliveryOrderItem::getTableName(), DeliveryOrder::getTableName('id'), '=', DeliveryOrderItem::getTableName('delivery_order_id'))
            ->groupBy('sales_order_item_id')
            ->select(DeliveryOrderItem::getTableName('sales_order_item_id'))
            ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
            ->whereIn('sales_order_item_id', $salesOrderItemIds)
            ->active()
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

    public static function create($data)
    {
        if (! empty($data['sales_contract_id'])) {
            $salesContract = SalesContract::findOrFail($data['sales_contract_id']);
        }
        // TODO validation customer_name is optional type non empty string
        if (empty($data['customer_name'])) {
            $customer = Customer::find($data['customer_id'], ['name']);
            $data['customer_name'] = $customer->name;
        }

        $salesOrder = new self;
        $salesOrder->fill($data);

        $amount = 0;
        $salesOrderItems = [];
        $salesOrderServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (! empty($items) && is_array($items)) {
            $itemIds = array_column($items, 'item_id');
            $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($items as $item) {
                // $item['item_name'] = $dbItems[$item['item_id']]->name;
                $salesOrderItem = new SalesOrderItem;
                $salesOrderItem->fill($item);
                $salesOrderItem->item_name = $dbItems[$item['item_id']]->name;
                array_push($salesOrderItems, $salesOrderItem);

                $amount = $item['quantity'] * ($salesOrderItem->price - ($item['discount_value'] ?? 0));
            }
        } else {
            // TODO throw error if $items and $services are empty or not an array
        }

        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (! empty($services) && is_array($services)) {
            $serviceIds = array_column($services, 'service_id');
            $dbServices = Service::whereIn('id', $serviceIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($services as $service) {
                $service['service_name'] = $dbServices[$service['service_id']]->name;
                $salesOrderService = new SalesOrderService;
                $salesOrderService->fill($service);
                array_push($salesOrderServices, $salesOrderService);

                $amount = $service['quantity'] * ($service['price'] - $service['discount_value'] ?? 0);
            }
        } else {
            // TODO throw error if $items and $services are empty or not an array
        }

        $amount -= $data['discount_value'] ?? 0;
        $amount += $data['delivery_fee'] ?? 0;

        if ($data['type_of_tax'] === 'exclude' && ! empty($data['tax'])) {
            $amount += $data['tax'];
        }

        $salesOrder->amount = $amount;
        $salesOrder->save();

        $salesOrder->items()->saveMany($salesOrderItems);
        $salesOrder->services()->saveMany($salesOrderServices);

        $form = new Form;
        $form->fillData($data, $salesOrder);

        // TODO validation if item_id trully belong to group on the contract
        if (isset($salesContract)) {
            $salesContract->updateIfDone();
        }

        return $salesOrder;
    }
}
