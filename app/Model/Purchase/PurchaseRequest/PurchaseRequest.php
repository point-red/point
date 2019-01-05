<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;

class PurchaseRequest extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'required_date',
        'employee_id',
        'supplier_id',
    ];

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
        return $this->hasMany(PurchaseOrder::class, 'purchase_request_id')
            ->join('forms', 'forms.id', '=', 'purchase_orders.form_id')
            ->where('forms.canceled', '=', 0)
            ->orWhereNull('forms.canceled');
    }

    public static function create($data)
    {
        $purchaseRequest = new PurchaseRequest;
        $purchaseRequest->fill($data);
        $purchaseRequest->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $purchaseRequest->id;
        $form->formable_type = PurchaseRequest::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'PR{y}{m}{increment=4}',
            null,
            isset($data['supplier_id']) ? $data['supplier_id'] : null
        );
        $form->save();

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
