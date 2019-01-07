<?php

namespace App\Model\Purchase\PurchaseInvoice;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

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

    public static function create($data)
    {
        $purchaseInvoice = new self;
        $purchaseInvoice->fill($data);
        $purchaseInvoice->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $purchaseInvoice->id;
        $form->formable_type = self::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'P-INVOICE{y}{m}{increment=4}',
            null,
            $purchaseReceive->supplier_id
        );
        $form->save();

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $purchaseInvoiceItem = new PurchaseInvoiceItem;
            $purchaseInvoiceItem->fill($item);
            $purchaseInvoiceItem->purchase_invoice_id = $purchaseInvoice->id;
            array_push($array, $purchaseInvoiceItem);
        }
        $purchaseInvoice->items()->saveMany($array);

        $array = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $purchaseInvoiceService = new PurchaseInvoiceService;
            $purchaseInvoiceService->fill($service);
            $purchaseInvoiceService->purchase_invoice_id = $purchaseInvoice->id;
            array_push($array, $purchaseInvoiceService);
        }
        $purchaseInvoice->services()->saveMany($array);

        $purchaseInvoice->form();

        return $purchaseInvoice;
    }
}
