<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\FormApproval;
use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Item;
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

    public function getRequiredDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setRequiredDateAttribute($value)
    {
        $this->attributes['required_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public $defaultNumberPrefix = 'PR';

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

    public static function create($data, $archivedForm = null)
    {
        $purchaseRequest = new self;

        if (empty($data['employee_name'])) {
            $employee = Employee::find($data['employee_id'], ['name']);
            $data['employee_name'] = $employee->name;
        }

        if (empty($data['supplier_name'])) {
            $supplier = Supplier::find($data['supplier_id'], ['name']);
            $data['supplier_name'] = $supplier->name;
        }

        $purchaseRequest->fill($data);
        $purchaseRequest->save();

        $amount = 0;
        $amount += self::addItems($purchaseRequest, get_if_set($data['items']));
        $amount += self::addServices($purchaseRequest, get_if_set($data['services']));

        $purchaseRequest->amount = $amount;
        $purchaseRequest->save();

        $form = new Form;
        $form->fillData($data, $purchaseRequest);
        if ($archivedForm) {
            $form->number = $archivedForm->number;
        }

        self::addApproval($form, get_if_set($data['approver_id']));

        return $purchaseRequest;
    }

    public static function addItems($purchaseRequest, $items = [])
    {
        $amount = 0;

        if (! empty($items) && is_array($items)) {
            $purchaseRequestItems = [];
            $itemIds = array_column($items, 'item_id');
            $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($items as $item) {
                $purchaseRequestItem = new PurchaseRequestItem;
                $purchaseRequestItem->fill($item);
                $purchaseRequestItem->item_name = $dbItems[$item['item_id']]->name;
                array_push($purchaseRequestItems, $purchaseRequestItem);

                $amount += $item['quantity'] * $item['price'];
            }
            $purchaseRequest->items()->saveMany($purchaseRequestItems);
        }

        return $amount;
    }

    public static function addServices($purchaseRequest, $services = [])
    {
        $amount = 0;

        if (! empty($services) && is_array($services)) {
            $purchaseRequestServices = [];
            $serviceIds = array_column($services, 'service_id');
            $dbServices = Service::whereIn('id', $serviceIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($services as $service) {
                $purchaseRequestService = new PurchaseRequestService;
                $purchaseRequestService->fill($service);
                $purchaseRequestService->service_name = $dbServices[$service['service_id']]->name;
                array_push($purchaseRequestServices, $purchaseRequestService);

                $amount += $service['quantity'] * $service['price'];
            }

            $purchaseRequest->services()->saveMany($purchaseRequestServices);
        }

        return $amount;
    }

    public static function addApproval($form, $approverId)
    {
        if (!empty($approverId)) {
            FormApproval::create($form->id, $approverId);
        } else {
            $form->approved = true;
            $form->save();
        }
    }

    public static function isAllowedToUpdate($purchaseRequest)
    {
        // Check if not referenced by purchase order
        if ($purchaseRequest->purchaseOrders->count()) {
            $purchaseOrders = [];

            foreach ($purchaseRequest->purchaseOrders as $purchaseOrder) {
                $purchaseOrders[$purchaseOrder->id] = $purchaseOrder->form->number;
            }

            return response()->json([
                'code' => 422,
                'message' => 'Cannot edit form because referenced by purchase order',
                'referenced_by' => $purchaseRequest->purchaseOrders,
            ], 422);
        }

        return [];
    }
}
