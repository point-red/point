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
        'supplier_id',
        'due_date',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

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
        $total = $items->reduce(function($carry, $item) {
            $subtotal = $item->quantity * ($item->price - $item->discount_value);
            return $carry + $subtotal;
        }, 0);

        $services = $this->services;
        $total = $services->reduce(function($carry, $service) {
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
        // TODO add validation to exclude : canceled / rejected / done purchase receives
        $purchaseReceives = PurchaseReceive::whereIn('id', $data['purchase_receive_ids'])
            ->with('items')
            ->with('services')
            ->get();

        $purchaseInvoice = new self;
        $purchaseInvoice->fill($data);
        foreach ($purchaseReceives as $purchaseReceive) {
            $purchaseInvoice->supplier_id = $purchaseReceive->supplier_id;
            break;
        }
        $purchaseInvoice->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $purchaseInvoice->id;
        $form->formable_type = self::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'PI{y}{m}{increment=4}',
            null,
            $purchaseInvoice->supplier_id
        );
        $form->save();

        $items = [];
        $dataItems = $data['items'] ?? [];
        foreach ($dataItems as $value) {
            $items[$value['item_id']]['price'] = $value['price'];
            $items[$value['item_id']]['discount_percent'] = $value['discount_percent'] ?? null;
            $items[$value['item_id']]['discount_value'] = $value['discount_value'] ?? 0;
            $items[$value['item_id']]['taxable'] = $value['taxable'] ?? true;
        }

        $array = [];
        foreach ($purchaseReceives as $purchaseReceive) {
            $purchaseReceive->form->done = true;
            $purchaseReceive->form->save();
            foreach ($purchaseReceive->items as $purchaseReceiveItem) {
                $purchaseInvoiceItem = new PurchaseInvoiceItem;
                $purchaseInvoiceItem->purchase_receive_id = $purchaseReceiveItem->purchase_receive_id;
                $purchaseInvoiceItem->purchase_receive_item_id = $purchaseReceiveItem->id;
                $purchaseInvoiceItem->item_id = $purchaseReceiveItem->item_id;
                $purchaseInvoiceItem->quantity = $purchaseReceiveItem->quantity;
                $purchaseInvoiceItem->unit = $purchaseReceiveItem->unit;
                $purchaseInvoiceItem->converter = $purchaseReceiveItem->converter;
                $purchaseInvoiceItem->price = $items[$purchaseReceiveItem->item_id]['price'];
                $purchaseInvoiceItem->discount_percent = $items[$purchaseReceiveItem->item_id]['discount_percent'];
                $purchaseInvoiceItem->discount_value = $items[$purchaseReceiveItem->item_id]['discount_value'];
                $purchaseInvoiceItem->taxable = $items[$purchaseReceiveItem->item_id]['taxable'];
                $purchaseInvoiceItem->purchase_invoice_id = $purchaseInvoice->id;
                array_push($array, $purchaseInvoiceItem);
            }
        }
        $purchaseInvoice->items()->saveMany($array);

        $services = [];
        $dataServices = $data['services'] ?? [];
        foreach ($dataServices as $value) {
            $services[$value['service_id']]['price'] = $value['price'];
            $services[$value['service_id']]['discount_percent'] = $value['discount_percent'] ?? null;
            $services[$value['service_id']]['discount_value'] = $value['discount_value'] ?? 0;
            $services[$value['service_id']]['taxable'] = $value['taxable'] ?? 0;
        }

        $array = [];
        foreach ($purchaseReceives as $purchaseReceive) {
            foreach ($purchaseReceive->services as $purchaseReceiveService) {
                $purchaseInvoiceService = new PurchaseInvoiceService;
                $purchaseInvoiceService->purchase_receive_service_id = $purchaseReceiveService->id;
                $purchaseInvoiceService->service_id = $purchaseReceiveService->service_id;
                $purchaseInvoiceService->quantity = $purchaseReceiveService->quantity;
                $purchaseInvoiceService->price = $services[$purchaseReceiveService->service_id]['price'];
                $purchaseInvoiceService->discount_percent = $services[$purchaseReceiveService->service_id]['discount_percent'];
                $purchaseInvoiceService->discount_value = $services[$purchaseReceiveService->service_id]['discount_value'];
                $purchaseInvoiceService->taxable = $services[$purchaseReceiveService->service_id]['taxable'];
                $purchaseInvoiceService->purchase_invoice_id = $purchaseInvoice->id;
                array_push($array, $purchaseInvoiceService);
            }
        }
        $purchaseInvoice->services()->saveMany($array);

        $purchaseInvoice->form();

        return $purchaseInvoice;
    }
}
