<?php

namespace App\Model\Purchase\PurchaseReceived;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;

class PurchaseReceived extends TransactionModel
{
    protected $connection = 'tenant';
    
    protected $table = 'purchase_received';

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
        return $this->hasMany(PurchaseReceivedItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseReceivedService::class);
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
        $purchaseReceived = new PurchaseReceived;
        $purchaseReceived->fill($data);
        if (!is_null($data['purchase_order_id'])) {
            $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
            $purchaseReceived->supplier_id = $purchaseOrder->supplier->id;
        }
        else {
            $purchaseReceived->supplier_id = $data['supplier_id'];
        }
        $purchaseReceived->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $purchaseReceived->id;
        $form->formable_type = PurchaseReceived::class;
        $form->generateFormNumber($data['number']);
        $form->save();

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $purchaseReceivedItem = new PurchaseReceivedItem;
            $purchaseReceivedItem->fill($item);
            $purchaseReceivedItem->purchase_received_id = $purchaseReceived->id;
            array_push($array, $purchaseReceivedItem);
        }
        $purchaseReceived->items()->saveMany($array);

        $array = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $purchaseReceivedService = new PurchaseReceivedService;
            $purchaseReceivedService->fill($service);
            $purchaseReceivedService->purchase_received_id = $purchaseReceived->id;
            array_push($array, $purchaseReceivedService);
        }
        $purchaseReceived->services()->saveMany($array);

        $purchaseReceived->form();

        return $purchaseReceived;
    }
}
