<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;

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
        $purchaseOrder = PurchaseOrder::findOrFail($data['purchase_order_id']);

        $purchaseReceive = new self;
        $purchaseReceive->fill($data);
        $purchaseReceive->supplier_id = $purchaseOrder->supplier->id;
        $purchaseReceive->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $purchaseReceive->id;
        $form->formable_type = self::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'P-RECEIVE{y}{m}{increment=4}',
            null,
            $purchaseReceive->supplier_id
        );
        $form->save();

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $purchaseReceiveItem = new PurchaseReceiveItem;
            $purchaseReceiveItem->fill($item);
            $purchaseReceiveItem->purchase_receive_id = $purchaseReceive->id;
            array_push($array, $purchaseReceiveItem);
        }
        $purchaseReceive->items()->saveMany($array);

        $array = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $purchaseReceiveService = new PurchaseReceiveService;
            $purchaseReceiveService->fill($service);
            $purchaseReceiveService->purchase_receive_id = $purchaseReceive->id;
            array_push($array, $purchaseReceiveService);
        }
        $purchaseReceive->services()->saveMany($array);

        // Make form done when all item received
        $done = true;

        $purchaseOrderItemIds = array_column($purchaseOrder->items->toArray(), 'id');

        $tempArray = PurchaseReceiveItem::whereIn('purchase_order_item_id', $purchaseOrderItemIds)
            ->join(PurchaseReceive::getTableName(), PurchaseReceive::getTableName().'.id', '=', 'purchase_receive_items.purchase_receive_id')
            ->join(Form::getTableName(), PurchaseReceive::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->groupBy('purchase_order_item_id')
            ->select('purchase_receive_items.purchase_order_item_id')
            ->addSelect(\DB::raw('SUM(quantity) AS sum_received'))
            ->where(function($query) {
                $query->where(Form::getTableName().'.canceled', false)
                    ->orWhereNull(Form::getTableName().'.canceled');
            })->where(function($query) {
                $query->where(Form::getTableName().'.approved', true)
                    ->orWhereNull(Form::getTableName().'.approved');
            })->get();

        $quantityReceivedItems = [];

        foreach ($tempArray as $value) {
            $quantityReceivedItems[$value['purchase_order_item_id']] = $value['sum_received'];
        }

        foreach ($purchaseOrder->items as $purchaseOrderItem) {
            $quantityReceived = $quantityReceivedItems[$purchaseOrderItem->id] ?? 0;
            if ($purchaseOrderItem->quantity - $quantityReceived > 0) {
                $done = false;
                break;
            }
        }

        if ($done == true) {
            $purchaseOrder->form->done = true;
            $purchaseOrder->form->save();
        }

        $purchaseReceive->form();

        return $purchaseReceive;
    }
}
