<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;
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
        $array = [];
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

        foreach ($data['items'] as $value) {
            $items[$value['item_id']]['price'] = $value['price'];
            $items[$value['item_id']]['discount_percent'] = $value['discount_percent'];
            $items[$value['item_id']]['discount_value'] = $value['discount_value'];
            $items[$value['item_id']]['taxable'] = $value['taxable'];
        }

        foreach ($purchaseReceives as $purchaseReceive) {
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

        foreach ($data['services'] as $value) {
            $services[$value['service_id']]['price'] = $value['price'];
            $services[$value['service_id']]['discount_percent'] = $value['discount_percent'];
            $services[$value['service_id']]['discount_value'] = $value['discount_value'];
            $services[$value['service_id']]['taxable'] = $value['taxable'];
        }

        $array = [];
        foreach ($purchaseReceives as $purchaseReceive) {
            foreach ($purchaseReceive->services as $purchaseReceiveService) {
                $purchaseInvoiceService = new PurchaseInvoiceService;
                $purchaseInvoiceService->purchase_order_item_id = $purchaseReceiveService->purchase_order_item_id;
                $purchaseInvoiceService->item_id = $purchaseReceiveService->item_id;
                $purchaseInvoiceService->quantity = $purchaseReceiveService->quantity;
                $purchaseInvoiceService->unit = $purchaseReceiveService->unit;
                $purchaseInvoiceService->converter = $purchaseReceiveService->converter;
                $purchaseInvoiceService->price = $items[$purchaseReceiveItem->item_id]['price'];
                $purchaseInvoiceService->discount_percent = $items[$purchaseReceiveItem->item_id]['discount_percent'];
                $purchaseInvoiceService->discount_value = $items[$purchaseReceiveItem->item_id]['discount_value'];
                $purchaseInvoiceService->taxable = $items[$purchaseReceiveItem->item_id]['taxable'];
                $purchaseInvoiceService->purchase_invoice_id = $purchaseInvoice->id;
                array_push($array, $purchaseInvoiceService);
            }
        }
        $purchaseInvoice->services()->saveMany($array);

        $purchaseInvoice->form();

        return $purchaseInvoice;
    }
}
