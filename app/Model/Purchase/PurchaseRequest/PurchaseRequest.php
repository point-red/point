<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Item;
use App\Model\Master\Service;
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
        'employee_name',
        'supplier_id',
        'supplier_name',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

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

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->joinForm(PurchaseOrder::class)
            ->active();
    }

    public static function create($data)
    {
        $purchaseRequest = new self;
        // TODO validation employee_name is optional type non empty string
        if (empty($data['employee_name'])) {
            $employee = Employee::find($data['employee_id'], ['name']);
            $data['supplier_name'] = $employee->name;
        }
        // TODO validation supplier_name is optional type non empty string
        if (empty($data['supplier_name'])) {
            $supplier = Supplier::find($data['supplier_id'], ['name']);
            $data['supplier_name'] = $supplier->name;
        }
        $purchaseRequest->fill($data);

        $amount = 0;
        $purchaseRequestItems = [];
        $purchaseRequestServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $itemIds = array_column($items, 'item_id');
            $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($items as $item) {
                $purchaseRequestItem = new PurchaseRequestItem;
                $purchaseRequestItem->fill($item);
                $purchaseRequestItem->item_name = $dbItems[$item['item_id']]->name;
                array_push($purchaseRequestItems, $purchaseRequestItem);

                $amount += $item['quantity'] * $item['price'];
            }
        }
        else {
            // TODO throw error if $items is empty or not an array
        }
        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            $serviceIds = array_column($services, 'service_id');
            $dbServices = Service::whereIn('id', $serviceIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($services as $service) {
                $purchaseRequestService = new PurchaseRequestService;
                $purchaseRequestService->fill($service);
                $purchaseRequestService->service_name = $dbServices[$service['service_id']]->name;
                array_push($purchaseRequestServices, $purchaseRequestService);

                $amount += $service['quantity'] * $service['price'];
            }
        }
        else {
            // TODO throw error if $services is empty or not an array
        }

        $purchaseRequest->amount = $amount;
        $purchaseRequest->save();

        $purchaseRequest->items()->saveMany($purchaseRequestItems);
        $purchaseRequest->services()->saveMany($purchaseRequestServices);

        $form = new Form;
        $form->fillData($data, $purchaseRequest);

        return $purchaseRequest;
    }
}
