<?php

namespace App\Model\Sales\SalesOrder;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use App\Model\TransactionModel;

class SalesOrder extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'sales_quotation_id',
        'sales_contract_id',
        'customer_id',
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
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
    ];

    protected $defaultNumberPrefix = 'SO';

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
        $salesOrder = new self;
        $salesOrder->fill($data);
        $salesOrder->save();

        $form = new Form;
        $form->fillData($data, $salesOrder);

        // TODO validation items is optional and must be array
        $array = [];
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $salesOrderItem = new SalesOrderItem;
                $salesOrderItem->fill($item);
                $salesOrderItem->sales_order_id = $salesOrder->id;
                array_push($array, $salesOrderItem);
            }
            $salesOrder->items()->saveMany($array);
        }

        // TODO validation services is required if items is null and must be array
        $array = [];
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            foreach ($services as $service) {
                $salesOrderService = new SalesOrderService;
                $salesOrderService->fill($service);
                $salesOrderService->sales_order_id = $salesOrder->id;
                array_push($array, $salesOrderService);
            }
            $salesOrder->services()->saveMany($array);
        }

        return $salesOrder;
    }
}
