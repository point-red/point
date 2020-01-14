<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Exceptions\IsReferencedException;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;

class PurchaseReceive extends TransactionModel
{
    public static $morphName = 'PurchaseReceive';

    protected $connection = 'tenant';

    protected $table = 'purchase_receives';

    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'supplier_address',
        'supplier_phone',
        'warehouse_id',
        'warehouse_name',
        'purchase_order_id',
        'driver',
        'license_plate',
    ];

    public $defaultNumberPrefix = 'RECEIVE';

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

    public function purchaseInvoices()
    {
        return $this->belongsToMany(PurchaseInvoice::class, 'purchase_invoice_items')->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by purchase invoice
        if ($this->purchaseInvoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase receive', $this->purchaseInvoices);
        }
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by purchase invoice
        if ($this->purchaseInvoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase receive', $this->purchaseInvoices);
        }
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
        return array_map(function ($item) {
            $purchaseReceiveItem = new PurchaseReceiveItem;
            $purchaseReceiveItem->fill($item);

            return $purchaseReceiveItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
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
            if ($item->quantity > 0) {
                $totalPerItem = ($item->price - $item->discount_value) * $item->quantity * $item->converter;
                $feePerItem = $totalPerItem / $totalItemsAmount * $additionalFee;
                $price = ($totalPerItem + $feePerItem) / $item->quantity;
                $options = [];
                if ($item->expiry_date) {
                    $options['expiry_date'] = $item->expiry_date;
                }
                if ($item->production_number) {
                    $options['production_number'] = $item->production_number;
                }
                InventoryHelper::increase($form->id, $purchaseReceive->warehouse_id, $item->item_id, $item->quantity, $price, $options);
            }
        }
    }
}
