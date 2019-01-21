<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;

class PurchaseRequest extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'required_date',
        'employee_id',
        'supplier_id',
    ];

    protected $defaultNumberPrefix = 'PR';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseRequestService::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->join(Form::getTableName(), Form::getTableName('id'), '=', PurchaseOrder::getTableName('form_id'))
            ->isActive();
    }

    public static function create($data)
    {
        $purchaseRequest = new self;
        $purchaseRequest->fill($data);
        $purchaseRequest->save();

        $form = new Form;
        $form->fillData($data, $purchaseRequest);

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $purchaseRequestItem = new PurchaseRequestItem;
            $purchaseRequestItem->fill($item);
            $purchaseRequestItem->purchase_request_id = $purchaseRequest->id;
            array_push($array, $purchaseRequestItem);
        }
        $purchaseRequest->items()->saveMany($array);

        $array = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $purchaseRequestService = new PurchaseRequestService;
            $purchaseRequestService->fill($service);
            $purchaseRequestService->purchase_request_id = $purchaseRequest->id;
            array_push($array, $purchaseRequestService);
        }
        $purchaseRequest->services()->saveMany($array);

        return $purchaseRequest;
    }
}
