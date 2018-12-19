<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\TransactionModel;
use Illuminate\Database\Eloquent\Model;

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

    public function form()
    {
        return $this->belongsTo(Form::class);
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {
        $form = new Form;
        $form->fill($data);
        $form->save();

        $purchaseOrder = new PurchaseOrder;
        $purchaseOrder->fill($data);
        $purchaseOrder->form_id = $form->id;
        $purchaseOrder->save();

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $purchaseOrderitem = new PurchaseOrderItem;
            $purchaseOrderitem->fill($item);
            $purchaseOrderitem->purchase_order_id = $purchaseOrder->id;
            array_push($array, $purchaseOrderitem);
        }
        $purchaseOrder->items()->saveMany($array);

        $array    = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $purchaseOrderService = new PurchaseOrderService;
            $purchaseOrderService->fill($service);
            $purchaseOrderService->purchase_order_id = $purchaseOrder->id;
            array_push($array, $purchaseOrderService);
        }
        $purchaseOrder->services()->saveMany($array);

        return $purchaseOrder;
    }
}
