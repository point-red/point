<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Service;
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

        // TODO validation supplier_name is optional type non empty string
        if (empty($data['supplier_name'])) {
            $supplier = Supplier::find($data['supplier_id'], ['name']);
            $data['supplier_name'] = $supplier->name;
        }

        $purchaseOrder->fill($data);

        $amount = 0;
        $purchaseOrderItems = [];
        $purchaseOrderServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $itemIds = array_column($items, 'item_id');
            $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($items as $item) {
                $purchaseOrderItem = new PurchaseOrderItem;
                $purchaseOrderItem->fill($item);
                $purchaseOrderItem->item_name = $dbItems[$item['item_id']]->name;
                array_push($purchaseOrderItems, $purchaseOrderItem);

                $amount += $item['quantity'] * ($item['price'] - $item['discount_value'] ?? 0);
            }
        }
        else {
            // TODO throw error if $items is not an array
        }
        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            $serviceIds = array_column($services, 'service_id');
            $dbServices = Service::whereIn('id', $serviceIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($services as $service) {
                $purchaseOrderService = new PurchaseOrderService;
                $purchaseOrderService->fill($service);
                $purchaseOrderService->service_name = $dbServices[$service['service_id']]->name;
                array_push($purchaseOrderServices, $purchaseOrderService);

                $amount += $service['quantity'] * ($service['price'] - $service['discount_value'] ?? 0);
            }
        }
        else {
            // TODO throw error if $services is not an array
        }

        $amount -= $data['discount_value'] ?? 0;
        $amount += $data['delivery_fee'] ?? 0;

        if ($data['type_of_tax'] === 'exclude' && !empty($data['tax'])) {
            $amount += $data['tax'];
        }

        $purchaseOrder->amount = $amount;
        $purchaseOrder->save();

        $purchaseOrder->items()->saveMany($purchaseOrderItems);
        $purchaseOrder->services()->saveMany($purchaseOrderServices);

        $form = new Form;
        $form->fillData($data, $purchaseOrder);

        return $purchaseOrder;
    }
}
