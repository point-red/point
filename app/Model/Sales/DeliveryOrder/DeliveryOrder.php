<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\DeliveryOrderJoin;
use App\Traits\Model\Sales\DeliveryOrderRelation;

class DeliveryOrder extends TransactionModel
{
    use DeliveryOrderJoin, DeliveryOrderRelation;

    public static $morphName = 'SalesDeliveryOrder';

    protected $connection = 'tenant';

    public static $alias = 'sales_delivery_order';

    protected $table = 'delivery_orders';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_id',
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'warehouse_id',
        'billing_address',
        'billing_phone',
        'billing_email',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
    ];

    public $defaultNumberPrefix = 'DO';

    public function isComplete()
    {
        if ($this->items->count() === 0) {
            return false;
        }

        $complete = true;
        foreach ($this->items as $item) {
            foreach ($item->deliveryNoteItems as $orderItem) {                
                if ($orderItem->deliveryNote->form->cancellation_status == null
                    || $orderItem->deliveryNote->form->cancellation_status !== 1
                    || $orderItem->deliveryNote->form->number !== null) {
                        $quantityNote = $item->deliveryNoteItems->sum('quantity');
                        if ($item->quantity > $quantityNote) {
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
        // Check if not referenced by delivery notes
        if ($this->deliveryNotes->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by delivery note(s)', $this->deliveryNotes);
        }
    }

    public static function create($data)
    {
        $deliveryOrder = new self;
        $deliveryOrder->fill($data);
        $deliveryOrder->save();

        $items = self::mapItems($data['items']);
        $deliveryOrder->items()->saveMany($items);
        
        $form = new Form;
        $form->saveData($data, $deliveryOrder);
        
        $salesOrder = $deliveryOrder->salesOrder;
        if ($salesOrder) {
            $salesOrder->updateStatus();
        }

        return $deliveryOrder;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $deliveryOrderItem = new DeliveryOrderItem;
            $deliveryOrderItem->fill($item);

            return $deliveryOrderItem;
        }, $items);
    }
}
