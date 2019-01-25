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
        $salesQuotation->save();

        $form = new Form;
        $form->fillData($data, $salesQuotation);

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $salesQuotationItem = new SalesQuotationItem;
            $salesQuotationItem->fill($item);
            $salesQuotationItem->sales_quotation_id = $salesQuotation->id;
            array_push($array, $salesQuotationItem);
        }
        $salesQuotation->items()->saveMany($array);

        $array = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $salesQuotationService = new SalesQuotationService;
            $salesQuotationService->fill($service);
            $salesQuotationService->sales_quotation_id = $salesQuotation->id;
            array_push($array, $salesQuotationService);
        }
        $salesQuotation->services()->saveMany($array);

        return $salesQuotation;
    }
}
