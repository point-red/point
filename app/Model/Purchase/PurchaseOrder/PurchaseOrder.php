<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\TransactionModel;
use App\Traits\Model\Purchase\PurchaseOrderJoin;
use App\Traits\Model\Purchase\PurchaseOrderRelation;
use Carbon\Carbon;

class PurchaseOrder extends TransactionModel
{
    use PurchaseOrderJoin, PurchaseOrderRelation;

    public static $morphName = 'PurchaseOrder';

    protected $connection = 'tenant';

    public static $alias = 'purchase_order';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id',
        'purchase_contract_id',
        'supplier_id',
        'supplier_name',
        'supplier_address',
        'supplier_phone',
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

        $purchaseOrder->amount = self::calculateAmount($purchaseOrder, $items);
        $purchaseOrder->save();

        $purchaseOrder->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $purchaseOrder);

        if (get_if_set($data['purchase_request_id'])) {
            $done = true;
            $purchaseRequest = PurchaseRequest::findOrFail($data['purchase_request_id']);
            foreach($purchaseRequest->items as $purchaseRequestItem) {
                $quantity = PurchaseOrderItem::where('purchase_request_item_id', $purchaseRequestItem->id)->sum('quantity');
                if ($quantity < $purchaseRequestItem->quantity) {
                    $done = false;
                    break;
                }
            }
            if ($done) {
                $purchaseRequest->form()->update(['done' => $done]);
            }
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

    private static function calculateAmount($purchaseOrder, $items)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * ($item->price - $item->discount_value);
        }, 0);

        $amount -= $purchaseOrder->discount_value;
        $amount += $purchaseOrder->delivery_fee;

        if ($purchaseOrder->type_of_tax === 'exclude') {
            $amount += $purchaseOrder->tax;
        }

        return $amount;
    }
}
