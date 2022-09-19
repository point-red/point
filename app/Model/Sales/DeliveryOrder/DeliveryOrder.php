<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\DeliveryOrderJoin;
use App\Traits\Model\Sales\DeliveryOrderRelation;
use Exception;
use Illuminate\Support\Arr;

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

        $quantity = [];
        foreach ($this->items as $item) {
            $quantityNote = 0;
            $quantityOrder = 0;
            $deliveryNoteId = 0;
            foreach ($item->deliveryNoteItems as $orderItem) {
                if (($orderItem->deliveryNote->form->cancellation_status == null
                    || $orderItem->deliveryNote->form->cancellation_status !== 1)
                    && $orderItem->deliveryNote->form->number !== null
                    && $deliveryNoteId != $orderItem->deliveryNote->id) {
                    $deliveryNoteId = $orderItem->deliveryNote->id;
                    if (! DeliveryNote::isFormApproved($orderItem->deliveryNote->form)) {
                        return false;
                    }
                    if ($quantityOrder == 0) {
                        $quantityOrder = (int) $item->quantity_delivered;
                    }
                    $quantityNote += (int) $item->deliveryNoteItems
                        ->where('delivery_note_id', $orderItem->deliveryNote->id)
                        ->sum('quantity');
                }
            }
            $quantity[] = [
                'dn_total' => $quantityNote,
                'do_total' => $quantityOrder,
            ];
        }

        foreach ($quantity as $delivery) {
            if ($delivery['do_total'] > $delivery['dn_total']) {
                return false;
            }
        }

        return true;
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
        $this->formStateActivePending();

        $this->isNotReferenced();
    }

    private function formStateActivePending()
    {
        $formIsActivePending = self::from(DeliveryOrder::getTableName().' as '.DeliveryOrder::$alias)
            ->where(DeliveryOrder::$alias.'.id', $this->attributes['id']);

        $formIsActivePending = self::joins($formIsActivePending, 'form')
            ->activePending()
            ->first();

        if (! $formIsActivePending) {
            throw new Exception('Delivery order not active and in pending state');
        }
    }

    private function isNotReferenced()
    {
        // Check if not referenced by delivery notes
        if ($this->deliveryNotes->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by delivery note(s)', $this->deliveryNotes);
        }
    }

    public function checkQuantityOver($requestDeliveryOrderItems)
    {
        foreach ($this->salesOrder->items as $salesOrderItem) {
            $salesOrderItem->convertUnitToSmallest();

            $requestDeliveryOrderItem = Arr::first($requestDeliveryOrderItems, function ($item, $key) use ($salesOrderItem) {
                return $item->item_id === $salesOrderItem->item_id;
            });

            $deliveredOrderItemQuantity = $salesOrderItem->deliveryOrderItemsOrdered();
            // delivery order item unit always in smallest unit. should'nt convert
            if ($salesOrderItem->quantity_remaining < ($requestDeliveryOrderItem->quantity_delivered + $deliveredOrderItemQuantity)) {
                throw new Exception("Delivery order item can't exceed sales order request", 422);
            }
        }
    }

    public static function create($data)
    {
        $items = self::mapItems($data['items']);

        $deliveryOrder = new self;
        $deliveryOrder->fill($data);

        $deliveryOrder->checkQuantityOver($items);

        $deliveryOrder->save();
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
