<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\FormApproval;
use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Service;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;

class PurchaseRequest extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'required_date',
        'employee_id',
        'employee_name',
        'supplier_id',
        'supplier_name',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'PR';

    public function getRequiredDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setRequiredDateAttribute($value)
    {
        $this->attributes['required_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

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

    public function approvers()
    {
        return $this->hasManyThrough(FormApproval::class, Form::class, 'formable_id', 'form_id')->where('formable_type', PurchaseRequest::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->joinForm(PurchaseOrder::class)
            ->active();
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by purchase order
        if ($this->purchaseOrders->count()) {

            return response()->json([
                'code' => 422,
                'message' => 'Cannot edit form because referenced by purchase order',
                'referenced_by' => $this->purchaseOrders,
            ], 422);
        }

        return [];
    }

    public static function create($data)
    {
        $purchaseRequest = new self;
        $purchaseRequest->fill($data);

        $items = self::getItems($data['items'] ?? []);
        $services = self::getServices($data['services'] ?? []);

        $purchaseRequest->amount = self::getAmount($purchaseRequest, $items, $services);
        $purchaseRequest->save();

        $purchaseRequest->items()->saveMany($items);
        $purchaseRequest->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $purchaseRequest);

        return $purchaseRequest;
    }

    private static function getItems($items)
    {
        return array_map(function($item) {
            $purchaseRequestItem = new PurchaseRequestItem;
            $purchaseRequestItem->fill($item);

            return $purchaseRequestItem;
        }, $items);
    }

    private static function getServices($services)
    {
        return array_map(function($service) {
            $purchaseRequestService = new PurchaseRequestService;
            $purchaseRequestService->fill($service);

            return $purchaseRequestService;
        }, $services);
    }
}
