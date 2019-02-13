<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use App\Model\Master\Service;
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
        'supplier_name',
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

        if (!empty($data['purchase_order_id'])) {
            $purchaseOrder = PurchaseOrder::findOrFail($data['purchase_order_id']);
            $purchaseOrderItems = $purchaseOrder->items->keyBy('id');

            $purchaseOrderServices = $purchaseOrder->services->keyBy('id');
            // TODO maybe need to add additional check
            // if the $purchaseOrder canceled / rejected / archived
            $purchaseReceive->supplier_id = $purchaseOrder->supplier_id;
            $purchaseReceive->supplier_name = $purchaseOrder->supplier_name;
            $purchaseReceive->billing_address = $purchaseOrder->billing_address;
            $purchaseReceive->billing_phone = $purchaseOrder->billing_phone;
            $purchaseReceive->billing_email = $purchaseOrder->billing_email;
            $purchaseReceive->shipping_address = $purchaseOrder->shipping_address;
            $purchaseReceive->shipping_phone = $purchaseOrder->shipping_phone;
            $purchaseReceive->shipping_email = $purchaseOrder->shipping_email;
        }
        // TODO throw error if purchase_order_id and supplier_id both null
        else if (!empty($data['supplier_id'])) {
            // TODO validation supplier_name is optional non empty string
            if (empty($data['supplier_name'])) {
                $supplier = Supplier::find($data['supplier_id'], ['name']);
                $purchaseReceive->supplier_name = $supplier->name;
            }
        }

        $purchaseReceive->save();

        $form = new Form;
        $form->fillData($data, $purchaseReceive);

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $array = [];

            if (!empty($purchaseOrder)) {
                $itemIds = array_column($items, 'item_id');
                $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');
            }

            foreach ($items as $item) {
                $purchaseReceiveItem = new PurchaseReceiveItem;
                $purchaseReceiveItem->fill($item);

                $purchaseOrderItemId = $item['purchase_order_item_id'] ?? null;

                // TODO validation purchaseOrderItemId is optional and must be integer
                if (!empty($purchaseOrderItemId)) {
                    $purchaseOrderItem = $purchaseOrderItems[$purchaseOrderItemId];
                    $purchaseReceiveItem->item_id = $purchaseOrderItem->item_id;
                    $purchaseReceiveItem->item_name = $purchaseOrderItem->item_name;
                    $purchaseReceiveItem->price = $purchaseOrderItem->price;
                    $purchaseReceiveItem->discount_percent = $purchaseOrderItem->discount_percent;
                    $purchaseReceiveItem->discount_value = $purchaseOrderItem->discount_value;
                    $purchaseReceiveItem->allocation_id = $purchaseOrderItem->allocation_id;
                }
                // TODO validation item_id is required if no purchase order, and must be integer
                else {
                    $purchaseReceiveItem->item_id = $item['item_id'];
                    $purchaseReceiveItem->item_name = $dbItems[$item['item_id']]->name;
                }
                array_push($array, $purchaseReceiveItem);

                // Insert to inventories table
                $inventory = new Inventory;
                $inventory->form_id = $purchaseReceiveItem->form_id;
                $inventory->warehouse_id = $purchaseReceive->warehouse_id;
                $inventory->item_id = $purchaseReceiveItem->item_id;
                $inventory->quantity = $purchaseReceiveItem->quantity;
                $inventory->price = $purchaseReceiveItem->price;
                $inventory->cogs = $purchaseReceiveItem->cogs;
                $inventory->total_quantity = $purchaseReceiveItem->total_quantity;
                $inventory->total_value = $purchaseReceiveItem->total_value;
                $inventory->save();
            }

            $purchaseReceive->items()->saveMany($array);
        }

        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            $array = [];

            if (!empty($purchaseOrder)) {
                $serviceIds = array_column($services, 'service_id');
                $dbServices = Service::whereIn('id', $serviceIds)->select('id', 'name')->get()->keyBy('id');
            }

            foreach ($services as $service) {
                $purchaseReceiveService = new PurchaseReceiveService;
                $purchaseReceiveService->fill($service);

                $purchaseOrderServiceId = $service['purchase_order_service_id'];
                if (isset($purchaseOrderServices) && isset($purchaseOrderServices[$purchaseOrderServiceId])) {
                    $purchaseOrderService = $purchaseOrderServices[$purchaseOrderServiceId];
                    $purchaseReceiveService->service_id = $purchaseOrderService->service_id;
                    $purchaseReceiveService->service_name = $purchaseOrderService->service_name;
                    $purchaseReceiveService->price = $purchaseOrderService->price;
                    $purchaseReceiveService->discount_percent = $purchaseOrderService->discount_percent;
                    $purchaseReceiveService->discount_value = $purchaseOrderService->discount_value;
                    $purchaseReceiveService->allocation_id = $purchaseOrderService->allocation_id;
                }
                // TODO validation service_id is required if purchaseOrderItemId is null, and must be integer
                else {
                    $purchaseReceiveService->service_id = $service['service_id'];
                    $purchaseReceiveService->service_name = $dbServices[$service['service_id']]->name;
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
