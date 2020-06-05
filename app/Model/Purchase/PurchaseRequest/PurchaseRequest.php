<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Purchase\PurchaseRequestJoin;
use App\Traits\Model\Purchase\PurchaseRequestRelation;
use Carbon\Carbon;

class PurchaseRequest extends TransactionModel
{
    use PurchaseRequestJoin, PurchaseRequestRelation;

    public static $morphName = 'PurchaseRequest';

    public $timestamps = false;

    protected $connection = 'tenant';

    public static $alias = 'purchase_request';

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

    public $defaultNumberPrefix = 'PR';

    public function getRequiredDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setRequiredDateAttribute($value)
    {
        $this->attributes['required_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
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
        $this->isNotCanceled();
    }

    public static function create($data)
    {
        $purchaseRequest = new self;
        $purchaseRequest->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);
        $purchaseRequest->amount = self::calculateAmount($items, $services);
        $purchaseRequest->save();

        $purchaseRequest->items()->saveMany($items);
        $purchaseRequest->services()->saveMany($services);

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

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $purchaseRequestService = new PurchaseRequestService;
            $purchaseRequestService->fill($service);

            return $purchaseRequestService;
        }, $services);
    }

    private static function calculateAmount($items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * $item->converter * $item->price;
        });
        $amount += array_reduce($services, function ($carry, $service) {
            return $carry + $service->quantity * $service->converter * $service->price;
        });

        return $amount;
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->purchaseOrders->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase order', $this->purchaseOrders);
        }
    }

    public function updateIfDone()
    {
        $done = true;
        foreach ($this->items as $item) {
            $quantityOrdered = $item->purchaseOrderItems->sum('quantity');
            if ($item->quantity > $quantityOrdered) {
                $done = false;
                break;
            }
        }

        $this->form()->update(['done' => $done]);
    }
}
