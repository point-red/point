<?php

namespace App\Model\Sales\DeliveryNote;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\TransactionModel;
use App\Helpers\Inventory\InventoryHelper;

class DeliveryNote extends TransactionModel
{
    protected $connection = 'tenant';

    protected $table = 'delivery_notes';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'delivery_order_id',
        'driver',
        'license_plate',
    ];

    public $defaultNumberPrefix = 'DN';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(deliveryOrder::class, 'delivery_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($data['delivery_order_id']);
        // TODO add check if $deliveryOrder is canceled / rejected / archived

        $deliveryNote = new self;
        $deliveryNote->fill($data);
        $deliveryNote->customer_id = $deliveryOrder->customer_id;
        $deliveryNote->customer_name = $deliveryOrder->customer_name;
        $deliveryNote->billing_address = $deliveryOrder->billing_address;
        $deliveryNote->billing_phone = $deliveryOrder->billing_phone;
        $deliveryNote->billing_email = $deliveryOrder->billing_email;
        $deliveryNote->shipping_address = $deliveryOrder->shipping_address;
        $deliveryNote->shipping_phone = $deliveryOrder->shipping_phone;
        $deliveryNote->shipping_email = $deliveryOrder->shipping_email;
        $deliveryNote->save();

        $form = new Form;
        $form->fillData($data, $deliveryNote);

        // TODO items is required and must be array
        $array = [];
        $items = $data['items'];

        $deliveryOrderItems = $deliveryOrder->items->keyBy('id');

        foreach ($items as $item) {
            $deliveryOrderItem = $deliveryOrderItems[$item['delivery_order_item_id']];

            $deliveryNoteItem = new DeliveryNoteItem;
            $deliveryNoteItem->fill($item);
            $deliveryNoteItem->item_name = $deliveryOrderItem->item_name;
            $deliveryNoteItem->price = $deliveryOrderItem->price;
            $deliveryNoteItem->discount_percent = $deliveryOrderItem->discount_percent;
            $deliveryNoteItem->discount_value = $deliveryOrderItem->discount_value;
            $deliveryNoteItem->taxable = $deliveryOrderItem->taxable;
            $deliveryNoteItem->allocation_id = $deliveryOrderItem->allocation_id;
            array_push($array, $deliveryNoteItem);

            InventoryHelper::decrease($form->id, $deliveryNote->warehouse_id, $deliveryNoteItem);
        }
        $deliveryNote->items()->saveMany($array);

        $deliveryOrder->updateIfDone();

        return $deliveryNote;
    }
}
