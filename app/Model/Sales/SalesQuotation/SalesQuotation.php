<?php

namespace App\Model\Sales\SalesQuotation;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesQuotation extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'SQ';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItem::class);
    }

    public function services()
    {
        return $this->hasMany(SalesQuotationService::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'sales_quotation_id')
            ->join('forms', 'forms.id', '=', 'sales_orders.form_id')
            ->where('forms.canceled', false)
            ->orWhereNull('forms.canceled');
    }

    public static function create($data)
    {
        $salesQuotation = new self;

        // TODO validation customer_name is optional type non empty string
        if (empty($data['customer_name'])) {
            $customer = Customer::find($data['customer_id'], ['name']);
            $data['customer_name'] = $customer->name;
        }

        $salesQuotation->fill($data);

        $amount = 0;
        $salesQuotationItems = [];
        $salesQuotationServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $itemIds = array_column($items, 'item_id');
            $dbItems = Item::whereIn('id', $itemIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($items as $item) {
                $item['item_name'] = $dbItems[$item['item_id']]->name;
                $salesQuotationItem = new SalesQuotationItem;
                $salesQuotationItem->fill($item);
                array_push($salesQuotationItems, $salesQuotationItem);

                $amount += $item['quantity'] * $item['price'];
            }
        }
        else {
            // TODO throw error if $items is not an array
        }

        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($items) && is_array($items)) {
            $serviceIds = array_column($services, 'service_id');
            $dbServices = Service::whereIn('id', $serviceIds)->select('id', 'name')->get()->keyBy('id');

            foreach ($services as $service) {
                $service['service_name'] = $dbServices[$service['service_id']]->name;
                $salesQuotationService = new SalesQuotationService;
                $salesQuotationService->fill($service);
                array_push($salesQuotationServices, $salesQuotationService);

                $amount += $service['quantity'] * $service['price'];
            }
        }
        else {
            // TODO throw error if $services is not an array
        }

        $salesQuotation->amount = $amount;
        $salesQuotation->save();

        $salesQuotation->items()->saveMany($salesQuotationItems);
        $salesQuotation->services()->saveMany($salesQuotationServices);

        $form = new Form;
        $form->fillData($data, $salesQuotation);

        return $salesQuotation;
    }
}
