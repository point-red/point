<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\TransactionModel;

class SalesInvoice extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $appends = array('total', 'remaining_amount');

    protected $fillable = [
        'customer_id',
        'due_date',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

    protected $defaultNumberPrefix = 'INVOICE';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function services()
    {
        return $this->hasMany(SalesInvoiceService::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getTotalAttribute()
    {
        $items = $this->items;
        $total = $items->reduce(function ($carry, $item) {
            $subtotal = $item->quantity * ($item->price - $item->discount_value);

            return $carry + $subtotal;
        }, 0);

        $services = $this->services;
        $total = $services->reduce(function ($carry, $service) {
            $subtotal = $service->quantity * ($service->price - $service->discount_value);

            return $carry + $subtotal;
        }, $total);

        $total += $this->tax - $this->discount_value + $this->delivery_fee;

        return $total;
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total;
    }

    public static function create($data)
    {
        // TODO add validation to exclude : canceled / rejected / done sales receives
        $salesReceives = DeliveryNote::whereIn('id', $data['delivery_note_ids'])
            ->with('items')
            ->with('services')
            ->get();

        $salesInvoice = new self;
        $salesInvoice->fill($data);
        foreach ($salesReceives as $salesReceive) {
            $salesInvoice->customer_id = $salesReceive->customer_id;
            break;
        }
        $salesInvoice->save();

        $form = new Form;
        $form->fillData($data, $salesInvoice);

        $items = [];
        $dataItems = $data['items'] ?? [];
        foreach ($dataItems as $value) {
            $items[$value['item_id']]['price'] = $value['price'];
            $items[$value['item_id']]['discount_percent'] = $value['discount_percent'] ?? null;
            $items[$value['item_id']]['discount_value'] = $value['discount_value'] ?? 0;
            $items[$value['item_id']]['taxable'] = $value['taxable'] ?? true;
        }

        $array = [];
        foreach ($salesReceives as $salesReceive) {
            $salesReceive->form->done = true;
            $salesReceive->form->save();
            foreach ($salesReceive->items as $salesReceiveItem) {
                $salesInvoiceItem = new SalesInvoiceItem;
                $salesInvoiceItem->delivery_note_id = $salesReceiveItem->delivery_note_id;
                $salesInvoiceItem->delivery_note_item_id = $salesReceiveItem->id;
                $salesInvoiceItem->item_id = $salesReceiveItem->item_id;
                $salesInvoiceItem->quantity = $salesReceiveItem->quantity;
                $salesInvoiceItem->unit = $salesReceiveItem->unit;
                $salesInvoiceItem->converter = $salesReceiveItem->converter;
                $salesInvoiceItem->price = $items[$salesReceiveItem->item_id]['price'];
                $salesInvoiceItem->discount_percent = $items[$salesReceiveItem->item_id]['discount_percent'];
                $salesInvoiceItem->discount_value = $items[$salesReceiveItem->item_id]['discount_value'];
                $salesInvoiceItem->taxable = $items[$salesReceiveItem->item_id]['taxable'];
                $salesInvoiceItem->sales_invoice_id = $salesInvoice->id;
                array_push($array, $salesInvoiceItem);
            }
        }
        $salesInvoice->items()->saveMany($array);

        $services = [];
        $dataServices = $data['services'] ?? [];
        foreach ($dataServices as $value) {
            $services[$value['service_id']]['price'] = $value['price'];
            $services[$value['service_id']]['discount_percent'] = $value['discount_percent'] ?? null;
            $services[$value['service_id']]['discount_value'] = $value['discount_value'] ?? 0;
            $services[$value['service_id']]['taxable'] = $value['taxable'] ?? 0;
        }

        $array = [];
        foreach ($salesReceives as $salesReceive) {
            foreach ($salesReceive->services as $salesReceiveService) {
                $salesInvoiceService = new SalesInvoiceService;
                $salesInvoiceService->delivery_note_service_id = $salesReceiveService->id;
                $salesInvoiceService->service_id = $salesReceiveService->service_id;
                $salesInvoiceService->quantity = $salesReceiveService->quantity;
                $salesInvoiceService->price = $services[$salesReceiveService->service_id]['price'];
                $salesInvoiceService->discount_percent = $services[$salesReceiveService->service_id]['discount_percent'];
                $salesInvoiceService->discount_value = $services[$salesReceiveService->service_id]['discount_value'];
                $salesInvoiceService->taxable = $services[$salesReceiveService->service_id]['taxable'];
                $salesInvoiceService->sales_invoice_id = $salesInvoice->id;
                array_push($array, $salesInvoiceService);
            }
        }
        $salesInvoice->services()->saveMany($array);

        return $salesInvoice;
    }
}
