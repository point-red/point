<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;
use App\Exceptions\IsReferencedException;

class DeliveryOrder extends TransactionModel
{
    protected $connection = 'tenant';

    protected $table = 'delivery_orders';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'sales_order_id',
        'billing_address',
        'billing_phone',
        'billing_email',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
    ];

    public $defaultNumberPrefix = 'DO';

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

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class)->active();
    }

    public function updateIfDone()
    {
        $done = true;
        $items = $this->items()->with('deliveryNoteItems')->get();
        foreach ($items as $item) {
            $quantitySent = $item->deliveryNoteItems->sum('quantity');
            if ($item->quantity > $quantitySent) {
                $done = false;
                break;
            }
        }
        if ($done === true) {
            $this->form->done = true;
            $this->form->save();
        }
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by purchase order
        if ($this->deliveryNotes->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by delivery note', $this->deliveryNotes);
        }
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by purchase order
        if ($this->deliveryNotes->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by delivery note', $this->deliveryNotes);
        }
    }

    public static function create($data)
    {
        $salesOrder = SalesOrder::findOrFail($data['sales_order_id']);
        // TODO add check if $salesOrder is canceled / rejected / archived

        $deliveryOrder = new self;
        $deliveryOrder->fill($data);
        $deliveryOrder->customer_id = $salesOrder->customer_id;
        $deliveryOrder->customer_name = $salesOrder->customer_name;
        $deliveryOrder->save();

        $form = new Form;
        $form->saveData($data, $deliveryOrder);

        $items = self::mapItems($data['items'], $salesOrder);

        $deliveryOrder->items()->saveMany($items);

        $salesOrder->updateIfDone();

        return $deliveryOrder;
    }

    private static function mapItems($items, $salesOrder)
    {
        $salesOrderItems = $salesOrder->items;

        return array_map(function ($item) use ($salesOrderItems) {
            $salesOrderItem = $salesOrderItems->firstWhere('id', $item['sales_order_item_id']);

            $deliveryOrderItem = new DeliveryOrderItem;
            $deliveryOrderItem->fill($item);
            $deliveryOrderItem = self::setDeliveryOrderItem($deliveryOrderItem, $salesOrderItem);
            
            return $deliveryOrderItem;
        }, $items);
    }
    
    private static function setDeliveryOrderItem($deliveryOrderItem, $salesOrderItem)
    {
        $deliveryOrderItem->item_id = $salesOrderItem->item_id;
        $deliveryOrderItem->item_name = $salesOrderItem->item_name;
        $deliveryOrderItem->price = $salesOrderItem->price;
        $deliveryOrderItem->discount_percent = $salesOrderItem->discount_percent;
        $deliveryOrderItem->discount_value = $salesOrderItem->discount_value;
        $deliveryOrderItem->taxable = $salesOrderItem->taxable;
        $deliveryOrderItem->allocation_id = $salesOrderItem->allocation_id;

        return $deliveryOrderItem;
    }
}
