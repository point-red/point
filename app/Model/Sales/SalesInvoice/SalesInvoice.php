<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Finance\Payment\Payment;
use App\Model\Finance\Payment\PaymentBankIn;
use App\Model\Finance\Payment\PaymentCashIn;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesInvoice extends TransactionModel
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

    /**
     * Get the invoice's payment.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'referenceable');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount;
    }

    public function updateIfDone()
    {
        $paidAmount = Payment::join(Form::getTableName(), Payment::getTableName('id'), '=', Form::getTableName('formable_id'))
            ->where(function ($query) {
                $query->where(Form::getTableName('formable_type'), PaymentBankIn::class)
                    ->orWhere(Form::getTableName('formable_type'), PaymentCashIn::class);
            })
            ->join(PaymentDetail::getTableName(), Payment::getTableName('id'), '=', PaymentDetail::getTableName('payment_id'))
            ->where('referenceable_id', 1)
            ->where('referenceable_type', SalesInvoice::class)
            ->select(PaymentDetail::getTableName('amount'))
            ->active()
            ->get()
            ->sum('amount');

        if ($paidAmount >= $this->amount) {
            $this->form->done = true;
            $this->form->save();
        }
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

        $amount = 0;
        $salesInvoiceItems = [];
        $salesInvoiceServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $items = array_column($items, null, 'item_id');

            foreach ($deliveryNotes as $deliveryNote) {
                $deliveryNote->form()->update(['done' => true]);

                foreach ($deliveryNote->items as $deliveryNoteItem) {
                    $itemId = $deliveryNoteItem->item_id;
                    $item = $items[$itemId];

                    array_push($salesInvoiceItems, [
                        'delivery_note_id' => $deliveryNoteItem->delivery_note_id,
                        'delivery_note_item_id' => $deliveryNoteItem->id,
                        'item_id' => $itemId,
                        'item_name' => $deliveryNoteItem->item_name,
                        'quantity' => $deliveryNoteItem->quantity,
                        'unit' => $deliveryNoteItem->unit,
                        'converter' => $deliveryNoteItem->converter,
                        'price' => $item['price'],
                        'discount_percent' => $item['discount_percent'] ?? null,
                        'discount_value' => $item['discount_value'] ?? 0,
                        'taxable' => $item['taxable'],
                        'notes' => $item['notes'] ?? null,
                        'allocation_id' => $item['allocation_id'] ?? null,
                    ]);

                    $amount += $deliveryNoteItem->quantity * ($item['price'] - $item['discount_value'] ?? 0);
                }
            }
        }

        // TODO validation services is required only if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            $services = array_column($services, null, 'service_id');

            foreach ($salesOrders as $salesOrder) {
                foreach ($salesOrder->services as $salesOrderService) {
                    $serviceId = $salesOrderService->service_id;
                    $service = $services[$serviceId];

                    array_push($salesInvoiceServices, [
                        'sales_order_service_id' => $salesOrderService->id,
                        'service_id' => $salesOrderService->service_id,
                        'service_name' => $salesOrderService->service_name,
                        'quantity' => $salesOrderService->quantity,
                        'price' => $service['price'],
                        'discount_percent' => $service['discount_percent'] ?? null,
                        'discount_value' => $service['discount_value'] ?? 0,
                        'taxable' => $service['taxable'],
                        'notes' => $service['notes'] ?? null,
                        'allocation_id' => $service['allocation_id'] ?? null,
                    ]);

                    $amount += $deliveryNoteItem->quantity * ($service['price'] - $service['discount_value'] ?? 0);
                }
            }
        }

        $amount -= $data['discount_value'] ?? 0;
        $amount += $data['delivery_fee'] ?? 0;

        if ($data['type_of_tax'] === 'exclude' && !empty($data['tax'])) {
            $amount += $data['tax'];
        }

        $salesInvoice->amount = $amount;
        $salesInvoice->save();

        $salesInvoice->items()->createMany($salesInvoiceItems);
        $salesInvoice->services()->createMany($salesInvoiceServices);

        $form = new Form;
        $form->fillData($data, $salesInvoice);

        return $salesInvoice;
    }
}
