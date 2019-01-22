<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;

class PurchaseReceive extends TransactionModel
{
    protected $connection = 'tenant';

    protected $table = 'purchase_receives';

    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'purchase_order_id',
        'driver',
        'license_plate',
    ];

    protected $defaultNumberPrefix = 'P-RECEIVE';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiveItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseReceiveService::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {
        $purchaseReceive = new self;
        $purchaseReceive->fill($data);

        if (isset($data['purchase_order_id'])) {
            $purchaseOrder = PurchaseOrder::findOrFail($data['purchase_order_id']);
            $purchaseOrderItems = $purchaseOrder->items->toArray();
            $purchaseOrderItems = array_column($purchaseOrderItems, null, 'id');

            $purchaseOrderServices = $purchaseOrder->services->toArray();
            $purchaseOrderServices = array_column($purchaseOrderServices, null, 'id');
            // TODO maybe need to add additional check
            // if the $purchaseOrder canceled / rejected / archived
            $purchaseReceive->supplier_id = $purchaseOrder->supplier->id;
        }

        $purchaseReceive->save();

        $form = new Form;
        $form->fillData($data, $purchaseReceive);

        // TODO validation items is optional and must be array
        $array = [];
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $purchaseReceiveItem = new PurchaseReceiveItem;
                $purchaseReceiveItem->fill($item);
                $purchaseReceiveItem->purchase_receive_id = $purchaseReceive->id;

                $purchaseOrderItemId = $item['purchase_order_item_id'];
                if (isset($purchaseOrderItems) && isset($purchaseOrderItems[$purchaseOrderItemId])) {
                    $purchaseOrderItem = $purchaseOrderItems[$purchaseOrderItemId];
                    $purchaseReceiveItem->price = $purchaseOrderItem['price'];
                    $purchaseReceiveItem->discount_percent = $purchaseOrderItem['discount_percent'];
                    $purchaseReceiveItem->discount_value = $purchaseOrderItem['discount_value'];
                }
                array_push($array, $purchaseReceiveItem);
            }
            $purchaseReceive->items()->saveMany($array);
        }

        // TODO validation services is required if items is null and must be array
        $array = [];
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            foreach ($services as $service) {
                $purchaseReceiveService = new PurchaseReceiveService;
                $purchaseReceiveService->fill($service);
                $purchaseReceiveService->purchase_receive_id = $purchaseReceive->id;

                $purchaseOrderServiceId = $service['purchase_order_service_id'];
                if (isset($purchaseOrderServices) && isset($purchaseOrderServices[$purchaseOrderServiceId])) {
                    $purchaseOrderService = $purchaseOrderServices[$purchaseOrderServiceId];
                    $purchaseReceiveService->price = $purchaseOrderService['price'];
                    $purchaseReceiveService->discount_percent = $purchaseOrderService['discount_percent'];
                    $purchaseReceiveService->discount_value = $purchaseOrderService['discount_value'];
                }
                array_push($array, $purchaseReceiveService);
            }
            $purchaseReceive->services()->saveMany($array);
        }

        if (isset($purchaseOrder)) {
            $purchaseOrder->updateIfDone();
        }

        return $purchaseReceive;
    }
}
