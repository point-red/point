<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\TransactionModel;

class PurchaseInvoice extends TransactionModel
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

    protected $defaultNumberPrefix = 'PI';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseInvoiceService::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
        $purchaseReceives = PurchaseReceive::joinForm()
            ->active()
            ->notDone()
            ->select(PurchaseReceive::getTableName('*'))
            ->whereIn(PurchaseReceive::getTableName('id'), $data['purchase_receive_ids'])
            ->with('form', 'items', 'services')
            ->get();

        // TODO check if $purchaseReceives contains at least 1 record and return error if 0 records

        $purchaseInvoice = new self;
        $purchaseInvoice->fill($data);
        $purchaseInvoice->supplier_id = $purchaseReceives[0]->supplier_id;
        $purchaseInvoice->save();

        $form = new Form;
        $form->fillData($data, $purchaseInvoice);

        // TODO validation items is optional and must be array
        $dataItems = $data['items'] ?? [];
        if (!empty($dataItems) && is_array($dataItems)) {
            $items = array_column($dataItems, null, 'item_id');

            $array = [];
            foreach ($purchaseReceives as $purchaseReceive) {
                $purchaseReceive->form()->update(['done' => true]);

                foreach ($purchaseReceive->items as $purchaseReceiveItem) {
                    $itemId = $purchaseReceiveItem->item_id;
                    $item = $items[$itemId];

                    $purchaseInvoiceItem = new PurchaseInvoiceItem;
                    $purchaseInvoiceItem->purchase_receive_id = $purchaseReceiveItem->purchase_receive_id;
                    $purchaseInvoiceItem->purchase_receive_item_id = $purchaseReceiveItem->id;
                    $purchaseInvoiceItem->item_id = $itemId;
                    $purchaseInvoiceItem->quantity = $purchaseReceiveItem->quantity;
                    $purchaseInvoiceItem->unit = $purchaseReceiveItem->unit;
                    $purchaseInvoiceItem->converter = $purchaseReceiveItem->converter;
                    $purchaseInvoiceItem->price = $item['price'];
                    $purchaseInvoiceItem->discount_percent = $item['discount_percent'];
                    $purchaseInvoiceItem->discount_value = $item['discount_value'];
                    $purchaseInvoiceItem->taxable = $item['taxable'];
                    $purchaseInvoiceItem->purchase_invoice_id = $purchaseInvoice->id;
                    array_push($array, $purchaseInvoiceItem);
                }
            }
            $purchaseInvoice->items()->saveMany($array);
        }

        // TODO validation services is required if items is null and must be array
        $dataServices = $data['services'] ?? [];
        if (!empty($dataServices) && is_array($dataServices)) {
            $services = array_column($dataServices, null, 'service_id');

            $array = [];
            foreach ($purchaseReceives as $purchaseReceive) {
                $purchaseReceive->form()->update(['done' => true]);

                foreach ($purchaseReceive->services as $purchaseReceiveService) {
                    $serviceId = $purchaseReceiveService->service_id;
                    $service = $services[$serviceId];

                    $purchaseInvoiceService = new PurchaseInvoiceService;
                    $purchaseInvoiceService->purchase_receive_service_id = $purchaseReceiveService->id;
                    $purchaseInvoiceService->service_id = $serviceId;
                    $purchaseInvoiceService->quantity = $purchaseReceiveService->quantity;
                    $purchaseInvoiceService->price = $service['price'];
                    $purchaseInvoiceService->discount_percent = $service['discount_percent'];
                    $purchaseInvoiceService->discount_value = $service['discount_value'];
                    $purchaseInvoiceService->taxable = $service['taxable'];
                    $purchaseInvoiceService->purchase_invoice_id = $purchaseInvoice->id;

                    array_push($array, $purchaseInvoiceService);
                }
            }
            $purchaseInvoice->services()->saveMany($array);
        }

        return $purchaseInvoice;
    }
}
