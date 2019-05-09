<?php

namespace App\Model\Purchase\PurchaseOrder;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Exceptions\IsReferencedException;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;

class PurchaseOrder extends TransactionModel
{
    public static $morphName = 'PurchaseOrder';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id',
        'purchase_contract_id',
        'supplier_id',
        'supplier_name',
        'warehouse_id',
        'eta',
        'cash_only',
        'need_down_payment',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
        'billing_address',
        'billing_phone',
        'billing_email',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
    ];

    protected $casts = [
        'amount' => 'double',
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
        'need_down_payment' => 'double',
    ];

    public $defaultNumberPrefix = 'PO';

    public function getEtaAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setEtaAttribute($value)
    {
        $this->attributes['eta'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseOrderService::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function purchaseReceives()
    {
        return $this->hasMany(PurchaseReceive::class)->active();
    }

    public function downPayments()
    {
        return $this->morphMany(PurchaseDownPayment::class, 'downpaymentable')
            ->active();
    }

    public function paidDownPayments()
    {
        return $this->morphMany(PurchaseDownPayment::class, 'downpaymentable')
            ->active()
            ->whereNotNull('paid_by');
    }

    public function remainingDownPayments()
    {
        return $this->morphMany(PurchaseDownPayment::class, 'downpaymentable')
            ->active()
            ->where('remaining', '>', 0)
            ->whereNotNull('paid_by');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function updateIfDone()
    {
        // TODO check service too
        $done = true;
        $items = $this->items()->with('purchaseReceiveItems')->get();
        foreach ($items as $item) {
            $quantityReceived = $item->purchaseReceiveItems->sum('quantity');
            if ($item->quantity > $quantityReceived) {
                $done = false;
                break;
            }
        }

        $this->form()->update(['done' => $done]);
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by purchase order
        if ($this->purchaseReceives->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase receive', $this->purchaseReceives);
        }
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by purchase order
        if ($this->purchaseReceives->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase receive', $this->purchaseReceives);
        }

        // Check if not referenced by purchase order
        if ($this->downPayments->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by down payment', $this->downPayments);
        }
    }

    public static function create($data)
    {
        $purchaseOrder = new self;
        $purchaseOrder->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $purchaseOrder->amount = self::calculateAmount($purchaseOrder, $items, $services);
        $purchaseOrder->save();

        $purchaseOrder->items()->saveMany($items);
        $purchaseOrder->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $purchaseOrder);

        if (get_if_set($data['purchase_request_id'])) {
            $purchaseRequest = PurchaseRequest::findOrFail($data['purchase_request_id']);
            $purchaseRequest->updateIfDone();
        }

        return $purchaseOrder;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $purchaseOrderItem = new PurchaseOrderItem;
            $purchaseOrderItem->fill($item);

            return $purchaseOrderItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $purchaseOrderService = new PurchaseOrderService;
            $purchaseOrderService->fill($service);

            return $purchaseOrderService;
        }, $services);
    }

    private static function calculateAmount($purchaseOrder, $items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * ($item->price - $item->discount_value) * $item->converter;
        }, 0);

        $amount += array_reduce($services, function ($carry, $service) {
            return $carry + $service->quantity * ($service->price - $service->discount_value);
        }, 0);

        $amount -= $purchaseOrder->discount_value;
        $amount += $purchaseOrder->delivery_fee;

        if ($purchaseOrder->type_of_tax === 'exclude') {
            $amount += $purchaseOrder->tax;
        }

        return $amount;
    }
}
