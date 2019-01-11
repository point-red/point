<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class DeliveryOrder extends TransactionModel
{
    protected $connection = 'tenant';

    protected $table = 'delivery_orders';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'sales_order_id',
    ];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {
        $salesOrder = SalesOrder::findOrFail($data['sales_order_id']);

        $deliveryOrder = new self;
        $deliveryOrder->fill($data);
        $deliveryOrder->customer_id = $salesOrder->customer_id;
        $deliveryOrder->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $deliveryOrder->id;
        $form->formable_type = self::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'DO{y}{m}{increment=4}',
            $deliveryOrder->customer_id,
            null
        );
        $form->save();

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $deliveryOrderItem = new DeliveryOrderItem;
            $deliveryOrderItem->fill($item);
            $deliveryOrderItem->delivery_order_id = $deliveryOrder->id;
            $deliveryOrderItem->sales_order_id = $salesOrder->id;
            array_push($array, $deliveryOrderItem);
        }
        $deliveryOrder->items()->saveMany($array);

        // Make form done when all item delivered
        $done = true;

        $salesOrderItemIds = array_column($salesOrder->items->toArray(), 'id');

        $tempArray = DeliveryOrderItem::whereIn('sales_order_item_id', $salesOrderItemIds)
            ->join(DeliveryOrder::getTableName(), DeliveryOrder::getTableName().'.id', '=', 'delivery_order_items.delivery_order_id')
            ->join(Form::getTableName(), DeliveryOrder::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->groupBy('sales_order_item_id')
            ->select('delivery_order_items.sales_order_item_id')
            ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
            ->where(function($query) {
                $query->where(Form::getTableName().'.canceled', false)
                    ->orWhereNull(Form::getTableName().'.canceled');
            })->where(function($query) {
                $query->where(Form::getTableName().'.approved', true)
                    ->orWhereNull(Form::getTableName().'.approved');
            })->get();

        $quantityDeliveredItems = [];

        foreach ($tempArray as $value) {
            $quantityDeliveredItems[$value['sales_order_item_id']] = $value['sum_delivered'];
        }

        foreach ($salesOrder->items as $salesOrderItem) {
            $quantityDelivered = $quantityDeliveredItems[$salesOrderItem->id] ?? 0;
            if ($salesOrderItem->quantity - $quantityDelivered > 0) {
                $done = false;
                break;
            }
        }

        if ($done == true) {
            $salesOrder->form->done = true;
            $salesOrder->form->save();
        }

        $deliveryOrder->form();

        return $deliveryOrder;
    }
}
