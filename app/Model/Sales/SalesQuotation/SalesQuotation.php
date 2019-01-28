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

    protected $defaultNumberPrefix = 'SQ';

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
            foreach ($items as $item) {
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
            foreach ($services as $service) {
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
