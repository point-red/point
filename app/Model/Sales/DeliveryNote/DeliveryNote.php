<?php

namespace App\Model\Sales\DeliveryNote;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

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
        'customer_id',
        'customer_name',
        'billing_address',
        'billing_phone',
        'billing_email',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
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
        $deliveryNote = new self;
        $deliveryNote->fill($data);

        if (! empty($data['delivery_order_id'])) {
            $deliveryOrder = DeliveryOrder::findOrFail($data['delivery_order_id']);
            // TODO add check if $deliveryOrder is canceled / rejected / archived

            $deliveryNote->customer_id = $deliveryOrder->customer_id;
            $deliveryNote->customer_name = $deliveryOrder->customer_name;
            $deliveryNote->billing_address = $deliveryOrder->billing_address;
            $deliveryNote->billing_phone = $deliveryOrder->billing_phone;
            $deliveryNote->billing_email = $deliveryOrder->billing_email;
            $deliveryNote->shipping_address = $deliveryOrder->shipping_address;
            $deliveryNote->shipping_phone = $deliveryOrder->shipping_phone;
            $deliveryNote->shipping_email = $deliveryOrder->shipping_email;

            $deliveryOrderItems = $deliveryOrder->items->keyBy('id');
        } else if (empty($data['customer_name'])) {
            $customer = Customer::find($data['customer_id']);
            $deliveryNote->customer_name = $customer->name;
        }

        $deliveryNote->save();

        $form = new Form;
        $form->fillData($data, $deliveryNote);

        // TODO items is required and must be array
        $array = [];
        $items = $data['items'];
        if (empty($data['delivery_order_id'])) {
            $itemIds = array_column($data['items'], 'item_id');
            $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');
        }

        foreach ($items as $item) {
            $deliveryNoteItem = new DeliveryNoteItem;
            $deliveryNoteItem->fill($item);

            if (! empty($data['delivery_order_id'])) {
                $deliveryOrderItem = $deliveryOrderItems[$item['delivery_order_item_id']];

                $deliveryNoteItem->item_name = $deliveryOrderItem->item_name;
                $deliveryNoteItem->price = $deliveryOrderItem->price;
                $deliveryNoteItem->discount_percent = $deliveryOrderItem->discount_percent;
                $deliveryNoteItem->discount_value = $deliveryOrderItem->discount_value;
                $deliveryNoteItem->taxable = $deliveryOrderItem->taxable;
                $deliveryNoteItem->allocation_id = $deliveryOrderItem->allocation_id;
            } else {
                $deliveryNoteItem->item_name = $dbItems[$item['item_id']]->name;
            }
            array_push($array, $deliveryNoteItem);
            InventoryHelper::decrease($form->id, $deliveryNote->warehouse_id, $deliveryNoteItem);
        }
        $deliveryNote->items()->saveMany($array);

        if (! empty($data['delivery_order_id'])) {
            $deliveryOrder->updateIfDone();
        }

        return $deliveryNote;
    }
}
