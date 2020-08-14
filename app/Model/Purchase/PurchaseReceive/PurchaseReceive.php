<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Exceptions\IsReferencedException;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;
use App\Traits\Model\Purchase\PurchaseReceiveJoin;
use App\Traits\Model\Purchase\PurchaseReceiveRelation;

class PurchaseReceive extends TransactionModel
{
    use PurchaseReceiveJoin, PurchaseReceiveRelation;

    public static $morphName = 'PurchaseReceive';

    protected $connection = 'tenant';

    public static $alias = 'purchase_receive';

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
        $purchaseOrder = PurchaseOrder::findOrFail($data['purchase_order_id']);
        $purchaseReceive = self::fillDataFromPurchaseOrder($purchaseReceive, $purchaseOrder);

        $items = self::mapItems($data['items'] ?? []);

        $purchaseReceive->save();
        $purchaseReceive->items()->saveMany($items);

        $form = new Form;
        $form->approval_status = 1;
        $form->saveData($data, $purchaseReceive);

        $purchaseOrder->updateStatus();

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
        $array = [];
        foreach ($items as $item) {
            $itemModel = Item::find($item['item_id']);
            if ($itemModel->require_production_number || $itemModel->require_expiry_date) {
                if ($item['dna']) {
                    foreach ($item['dna'] as $dna) {
                        $dnaItem = $item;
                        $dnaItem['quantity'] = $dna['quantity'];
                        $dnaItem['production_number'] = $dna['production_number'];
                        $dnaItem['expiry_date'] = $dna['expiry_date'];
                        array_push($array, $dnaItem);
                    }
                }
            } else {
                array_push($array, $item);
            }
        }
        return array_map(function ($item) {
            $purchaseReceiveItem = new PurchaseReceiveItem;
            $purchaseReceiveItem->fill($item);
            return $purchaseReceiveItem;
        }, $array);
    }
}
