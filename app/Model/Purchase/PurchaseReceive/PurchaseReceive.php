<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;

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

    public $defaultNumberPrefix = 'P-RECEIVE';

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

    public function purchaseInvoice()
    {
        return $this->belongsToMany(PurchaseInvoice::class, 'purchase_invoice_items')->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function isAllowedToUpdate()
    {
        // TODO Check if not referenced by purchase invoice
    }

    public static function create($data)
    {
        $purchaseReceive = new self;
        $purchaseReceive->fill($data);

        if (! empty($data['purchase_order_id'])) {
            $purchaseOrder = PurchaseOrder::findOrFail($data['purchase_order_id']);
            $purchaseReceive = self::fillDataFromPurchaseOrder($purchaseReceive, $purchaseOrder);
        }

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $purchaseReceive->save();
        
        $purchaseReceive->items()->saveMany($items);
        $purchaseReceive->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $purchaseReceive);

        if (isset($purchaseOrder)) {
            $purchaseOrder->updateIfDone();
        }

        self::insertInventory($form, $purchaseOrder ?? null, $purchaseReceive);

        return $purchaseReceive;
    }

    private static function fillDataFromPurchaseOrder($purchaseReceive, $purchaseOrder)
    {
        $purchaseReceive->supplier_id = $purchaseOrder->supplier_id;
        $purchaseReceive->supplier_name = $purchaseOrder->supplier_name;
        $purchaseReceive->billing_address = $purchaseOrder->billing_address;
        $purchaseReceive->billing_phone = $purchaseOrder->billing_phone;
        $purchaseReceive->billing_email = $purchaseOrder->billing_email;
        $purchaseReceive->shipping_address = $purchaseOrder->shipping_address;
        $purchaseReceive->shipping_phone = $purchaseOrder->shipping_phone;
        $purchaseReceive->shipping_email = $purchaseOrder->shipping_email;

        return $purchaseReceive;
    }

    private static function mapItems($items)
    {
        return array_map(function($item) {
            $purchaseReceiveItem = new PurchaseReceiveItem;
            $purchaseReceiveItem->fill($item);

            return $purchaseReceiveItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function($service) {
            $purchaseReceiveServices = new PurchaseReceiveService;
            $purchaseReceiveServices->fill($service);

            return $purchaseReceiveServices;
        }, $services);
    }

    private static function insertInventory($form, $purchaseOrder, $purchaseReceive)
    {
        $additionalFee = 0;
        $totalItemsAmount = 1; // prevent division by 0

        if (! empty($purchaseOrder)) {
            $additionalFee = $purchaseOrder->delivery_fee - $purchaseOrder->discount_value;
            $totalItemsAmount = $purchaseOrder->amount - $additionalFee - $purchaseOrder->tax;
        }

        foreach ($purchaseReceive->items as $item) {
            $totalPerItem = ($item->price - $item->discount_value) * $item->quantity * $item->converter;
            $feePerItem = $totalPerItem / $totalItemsAmount * $additionalFee;
            $price = ($totalPerItem + $feePerItem) / $item->quantity;

            InventoryHelper::increase($form->id, $purchaseReceive->warehouse_id, $item->item_id, $item->quantity, $price);
        }
    }
}
