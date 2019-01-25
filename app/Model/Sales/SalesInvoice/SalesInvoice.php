<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesInvoice extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $appends = array('total', 'remaining_amount');

    protected $fillable = [
        'due_date',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

    protected $casts = [
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
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
        // TODO throw error if customer_id is not provided
        $customerId = $data['customer_id'] ?? null;

        if (!empty($data['delivery_note_ids']) && is_array($data['delivery_note_ids'])) {
            $deliveryNotes = DeliveryNote::joinForm()
                ->active()
                ->notDone()
                ->whereIn(DeliveryNote::getTableName('id'), $data['delivery_note_ids'])
                ->with('form', 'items')
                ->get();

            // TODO check if $deliveryNotes contains at least 1 record and return error if 0 records

            $customerId = $deliveryNotes[0]->customer_id;
        }
        else if (!empty($data['sales_order_ids']) && is_array($data['sales_order_ids'])) {
            $salesOrders = SalesOrder::joinForm()
                ->active()
                ->notDone()
                ->whereIn(SalesOrder::getTableName('id'), $data['sales_order_ids'])
                ->with('form', 'services')
                ->get();

            // TODO check if $salesOrders contains at least 1 record and return error if 0 records

            $customerId = $salesOrders[0]->customer_id;
        }

        // TODO throw error if $customerId is null or invalid id

        $salesInvoice = new self;
        $salesInvoice->fill($data);
        $salesInvoice->customer_id = $customerId;

        if (empty($data['customer_name'])) {
            $customer = Customer::find($customerId, ['name']);
            $salesInvoice->customer_name = $customer->name;
        }
        else {
            $salesInvoice->customer_name = $data['customer_name'];
        }

        $salesInvoice->save();

        $form = new Form;
        $form->fillData($data, $salesInvoice);

        // TODO validation items is optional and must be array
        $dataItems = $data['items'] ?? [];
        if (!empty($dataItems) && is_array($dataItems)) {
            $items = array_column($dataItems, null, 'item_id');

            $array = [];
            foreach ($deliveryNotes as $deliveryNote) {
                $deliveryNote->form()->update(['done' => true]);

                foreach ($deliveryNote->items as $deliveryNoteItem) {
                    $itemId = $deliveryNoteItem->item_id;
                    $item = $items[$itemId];

                    $salesInvoiceItem = new SalesInvoiceItem;
                    $salesInvoiceItem->delivery_note_id = $deliveryNoteItem->delivery_note_id;
                    $salesInvoiceItem->delivery_note_item_id = $deliveryNoteItem->id;
                    $salesInvoiceItem->item_id = $itemId;
                    $salesInvoiceItem->quantity = $deliveryNoteItem->quantity;
                    $salesInvoiceItem->unit = $deliveryNoteItem->unit;
                    $salesInvoiceItem->converter = $deliveryNoteItem->converter;
                    $salesInvoiceItem->price = $item['price'];
                    $salesInvoiceItem->discount_percent = $item['discount_percent'];
                    $salesInvoiceItem->discount_value = $item['discount_value'];
                    $salesInvoiceItem->taxable = $item['taxable'];
                    $salesInvoiceItem->sales_invoice_id = $salesInvoice->id;
                    array_push($array, $salesInvoiceItem);
                }
            }
            $salesInvoice->items()->saveMany($array);
        }

        // TODO validation services is required only if items is null and must be array
        $dataServices = $data['services'] ?? [];
        if (!empty($dataServices) && is_array($dataServices)) {
            $services = array_column($dataServices, null, 'service_id');

            $array = [];
            foreach ($salesOrders as $salesOrder) {
                foreach ($salesOrder->services as $salesOrderService) {
                    $serviceId = $salesOrderService->service_id;
                    $service = $services[$serviceId];

                    $salesInvoiceService = new SalesInvoiceService;
                    $salesInvoiceService->sales_order_service_id = $salesOrderService->id;
                    $salesInvoiceService->service_id = $salesOrderService->service_id;
                    $salesInvoiceService->quantity = $salesOrderService->quantity;
                    $salesInvoiceService->price = $service['price'];
                    $salesInvoiceService->discount_percent = $service['discount_percent'];
                    $salesInvoiceService->discount_value = $service['discount_value'];
                    $salesInvoiceService->taxable = $service['taxable'];
                    $salesInvoiceService->sales_invoice_id = $salesInvoice->id;
                    array_push($array, $salesInvoiceService);
                }
            }
            $salesInvoice->services()->saveMany($array);
        }

        return $salesInvoice;
    }
}
