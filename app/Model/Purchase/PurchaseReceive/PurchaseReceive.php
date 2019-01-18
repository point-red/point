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
            // TODO maybe need to add additional check
            // if the $purchaseOrder canceled / rejected / archived
            $purchaseReceive->supplier_id = $purchaseOrder->supplier->id;
        }

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

        // TODO validation items is optional and must be array
        $array = [];
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $purchaseReceiveItem = new PurchaseReceiveItem;
                $purchaseReceiveItem->fill($item);
                $purchaseReceiveItem->purchase_receive_id = $purchaseReceive->id;
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
