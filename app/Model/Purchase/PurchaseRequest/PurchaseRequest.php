<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Contracts\Model\Transaction;
use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Purchase\PurchaseRequestJoin;
use App\Traits\Model\Purchase\PurchaseRequestMutators;
use App\Traits\Model\Purchase\PurchaseRequestRelation;

class PurchaseRequest extends TransactionModel implements Transaction
{
    use PurchaseRequestJoin, PurchaseRequestRelation, PurchaseRequestMutators;

    protected $connection = 'tenant';

    protected $fillable = [
        'required_date',
        'supplier_id',
        'supplier_name',
        'supplier_address',
        'supplier_phone',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public static $morphName = 'PurchaseRequest';

    public static $alias = 'purchase_request';

    public $timestamps = false;

    public $defaultNumberPrefix = 'PR';

    public static function create($data)
    {
        $purchaseRequest = new self;
        $purchaseRequest->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $purchaseRequest->amount = self::calculateAmount($items);
        $purchaseRequest->save();

        $purchaseRequest->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $purchaseRequest);

        return $purchaseRequest;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $purchaseRequestItem = new PurchaseRequestItem;
            $purchaseRequestItem->fill($item);

            return $purchaseRequestItem;
        }, $items);
    }

    private static function calculateAmount($items)
    {
        return array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * $item->converter * $item->price;
        });
    }

    public function isAllowedToUpdate()
    {
        $this->isActive();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->isActive();
        $this->isNotReferenced();
        $this->isCancellationPending();
    }

    public function isComplete()
    {
        $complete = true;
        foreach ($this->items as $item) {
            foreach ($item->purchaseOrderItems as $orderItem) {
                if ($orderItem->purchaseOrder->form->cancellation_status == null
                    || $orderItem->purchaseOrder->form->cancellation_status !== 1
                    || $orderItem->purchaseOrder->form->number !== null) {
                        $quantityOrdered = $item->purchaseOrderItems->sum('quantity');
                        if ($item->quantity > $quantityOrdered) {
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

    public function updateReference()
    {
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->purchaseOrders->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase order', $this->purchaseOrders->load('form'));
        }
    }
}
