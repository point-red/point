<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Finance\Payment\Payment;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\TransactionModel;

class PurchaseInvoice extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'due_date',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

    protected $casts = [
        'tax' => 'double',
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
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

    /**
     * Get the invoice's payment.
     */
    public function payments()
    {
        return $this->morphMany(PaymentDetail::class, 'referenceable')
            ->join(Payment::getTableName(), Payment::getTableName('id'), '=', PaymentDetail::getTableName('payment_id'))
            ->joinForm(Payment::class)
            ->active();
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount;
    }

    public static function create($data)
    {
        $purchaseReceives = PurchaseReceive::joinForm()
            ->active()
            ->notDone()
            ->whereIn(PurchaseReceive::getTableName('id'), $data['purchase_receive_ids'])
            ->with('form', 'items', 'services')
            ->get();

        // TODO check if $purchaseReceives contains at least 1 record and return error if 0 records

        $purchaseInvoice = new self;
        $purchaseInvoice->fill($data);
        $purchaseInvoice->supplier_id = $purchaseReceives[0]->supplier_id;

        if (empty($data['supplier_name'])) {
            $supplier = Supplier::find($purchaseReceives[0]->supplier_id, ['name']);
            $purchaseInvoice->supplier_name = $supplier->name;
        }

        $amount = 0;
        $purchaseInvoiceItems = [];
        $purchaseInvoiceServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $items = array_column($items, null, 'item_id');
        }
        else {
            // TODO throw error if $items is empty or not an array
        }
        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            $services = array_column($services, null, 'service_id');
        }
        else {
            // TODO throw error if $services is empty or not an array
        }

        foreach ($purchaseReceives as $purchaseReceive) {
            $purchaseReceive->form()->update(['done' => true]);

            foreach ($purchaseReceive->items as $purchaseReceiveItem) {
                $itemId = $purchaseReceiveItem->item_id;
                $item = $items[$itemId];

                array_push($purchaseInvoiceItems, array(
                    'purchase_receive_id' => $purchaseReceiveItem->purchase_receive_id,
                    'purchase_receive_item_id' => $purchaseReceiveItem->id,
                    'item_id' => $itemId,
                    'item_name' => $purchaseReceiveItem->item_name,
                    'quantity' => $purchaseReceiveItem->quantity,
                    'unit' => $purchaseReceiveItem->unit,
                    'converter' => $purchaseReceiveItem->converter,
                    'price' => $item['price'],
                    'discount_percent' => $item['discount_percent'] ?? null,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'taxable' => $item['taxable'],
                    'notes' => $item['notes'] ?? null,
                    'allocation_id' => $item['allocation_id'] ?? null,
                ));

                $amount += $purchaseReceiveItem->quantity * ($item['price'] - $item['discount_value'] ?? 0);
            }

            foreach ($purchaseReceive->services as $purchaseReceiveService) {
                $serviceId = $purchaseReceiveService->service_id;
                $service = $services[$serviceId];

                array_push($purchaseInvoiceServices, [
                    'purchase_receive_id' => $purchaseReceiveService->purchase_receive_id,
                    'purchase_receive_service_id' => $purchaseReceiveService->id,
                    'service_id' => $serviceId,
                    'service_name' => $purchaseReceiveService->service_name,
                    'quantity' => $purchaseReceiveService->quantity,
                    'price' => $service['price'],
                    'discount_percent' => $service['discount_percent'] ?? null,
                    'discount_value' => $service['discount_value'] ?? 0,
                    'taxable' => $service['taxable'],
                    'notes' => $service['notes'] ?? null,
                    'allocation_id' => $service['allocation_id'] ?? null,
                ]);

                $amount += $purchaseReceiveService->quantity * ($service['price'] - $service['discount_value'] ?? 0);
            }
        }

        $amount -= $data['discount_value'] ?? 0;
        $amount += $data['delivery_fee'] ?? 0;

        if ($data['type_of_tax'] === 'exclude' && !empty($data['tax'])) {
            $amount += $data['tax'];
        }

        $purchaseInvoice->amount = $amount;
        $purchaseInvoice->save();

        $purchaseInvoice->items()->createMany($purchaseInvoiceItems);
        $purchaseInvoice->services()->createMany($purchaseInvoiceServices);

        $form = new Form;
        $form->fillData($data, $purchaseInvoice);

        return $purchaseInvoice;
    }
}
