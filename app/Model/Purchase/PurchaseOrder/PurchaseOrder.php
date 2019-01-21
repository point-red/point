<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\TransactionModel;

class PurchaseOrder extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id',
        'purchase_contract_id',
        'supplier_id',
        'warehouse_id',
        'eta',
        'cash_only',
        'need_down_payment',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

    protected $casts = [
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
    ];

    protected $defaultNumberPrefix = 'PO';

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
        return $this->hasMany(PurchaseReceive::class)
            ->joinForm(PurchaseReceive::class)
            ->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function updateIfDone()
    {
        $purchaseOrderItems = $this->items;
        $purchaseOrderItemIds = $purchaseOrderItems->pluck('id');

        $tempArray = PurchaseReceive::joinForm()
            ->join(PurchaseReceiveItem::getTableName(), PurchaseReceive::getTableName('id'), '=', PurchaseReceiveItem::getTableName('purchase_receive_id'))
            ->select(PurchaseReceiveItem::getTableName('purchase_order_item_id'))
            ->addSelect(\DB::raw('SUM(quantity) AS sum_received'))
            ->whereIn('purchase_order_item_id', $purchaseOrderItemIds)
            ->groupBy('purchase_order_item_id')
            ->active()
            ->get();

        $quantityReceivedItems = $tempArray->pluck('sum_received', 'purchase_order_item_id');

        // Make form done when all item received
        $done = true;
        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $quantityReceived = $quantityReceivedItems[$purchaseOrderItem->id] ?? 0;
            if ($purchaseOrderItem->quantity - $quantityReceived > 0) {
                $done = false;
                break;
            }
        }

        if ($done === true) {
            $this->form->done = true;
            $this->form->save();
        }
    }

    public static function create($data)
    {
        $purchaseOrder = new self;
        $purchaseOrder->fill($data);
        $purchaseOrder->save();

        $form = new Form;
        $form->fillData($data, $purchaseOrder);

        // TODO validation items is optional and must be array
        $array = [];
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $purchaseOrderItem = new PurchaseOrderItem;
                $purchaseOrderItem->fill($item);
                $purchaseOrderItem->purchase_order_id = $purchaseOrder->id;
                array_push($array, $purchaseOrderItem);
            }
            $purchaseOrder->items()->saveMany($array);
        }

        // TODO validation services is required if items is null and must be array
        $array = [];
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            foreach ($services as $service) {
                $purchaseOrderService = new PurchaseOrderService;
                $purchaseOrderService->fill($service);
                $purchaseOrderService->purchase_order_id = $purchaseOrder->id;
                array_push($array, $purchaseOrderService);
            }
            $purchaseOrder->services()->saveMany($array);
        }

        return $purchaseOrder;
    }
}
