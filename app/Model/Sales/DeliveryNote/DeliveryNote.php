<?php

namespace App\Model\Sales\DeliveryNote;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\DeliveryNoteJoin;
use App\Traits\Model\Sales\DeliveryNoteRelation;

class DeliveryNote extends TransactionModel
{
    use DeliveryNoteJoin, DeliveryNoteRelation;

    public static $morphName = 'SalesDeliveryNote';

    protected $connection = 'tenant';

    public static $alias = 'sales_delivery_note';

    protected $table = 'delivery_notes';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'delivery_order_id',
        'driver',
        'license_plate',
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'billing_address',
        'billing_phone',
        'billing_email',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
    ];

    public $defaultNumberPrefix = 'DN';

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

    private function isNotReferenced()
    {
        // Check if not referenced by sales invoice
        if ($this->salesInvoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by sales invoice(s)', $this->salesInvoices);
        }
    }

    public static function create($data)
    {
        $deliveryNote = new self;
        $deliveryNote->fill($data);

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

        $deliveryNote->save();

        $items = self::mapItems($data['items'] ?? [], $deliveryOrder);

        $deliveryNote->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $deliveryNote);

        $deliveryOrder->updateStatus();

        foreach ($items as $item) {
            $options = [];
            if ($item->expiry_date) {
                $options['expiry_date'] = $item->expiry_date;
            }
            if ($item->production_number) {
                $options['production_number'] = $item->production_number;
            }

            $options['quantity_reference'] = $item->quantity;
            $options['unit_reference'] = $item->unit;
            $options['converter_reference'] = $item->converter;
            InventoryHelper::decrease($form, $deliveryNote->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
        }

        return $deliveryNote;
    }

    private static function mapItems($items, $deliveryOrder)
    {
        $deliveryOrderItems = $deliveryOrder->items;

        return array_map(function ($item) use ($deliveryOrderItems) {
            $deliveryOrderItem = $deliveryOrderItems->firstWhere('id', $item['delivery_order_item_id']);

            $deliveryNoteItem = new DeliveryNoteItem;
            $deliveryNoteItem->fill($item);
            $deliveryNoteItem = self::setDeliveryNoteItem($deliveryNoteItem, $deliveryOrderItem);

            return $deliveryNoteItem;
        }, $items);
    }

    private static function setDeliveryNoteItem($deliveryNoteItem, $deliveryOrderItem)
    {
        $deliveryNoteItem->item_id = $deliveryOrderItem->item_id;
        $deliveryNoteItem->item_name = $deliveryOrderItem->item_name;
        $deliveryNoteItem->price = $deliveryOrderItem->price;
        $deliveryNoteItem->discount_percent = $deliveryOrderItem->discount_percent;
        $deliveryNoteItem->discount_value = $deliveryOrderItem->discount_value;
        $deliveryNoteItem->taxable = $deliveryOrderItem->taxable;
        $deliveryNoteItem->allocation_id = $deliveryOrderItem->allocation_id;

        return $deliveryNoteItem;
    }
}
