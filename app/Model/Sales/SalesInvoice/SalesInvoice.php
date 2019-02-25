<?php

namespace App\Model\Sales\SalesInvoice;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\TransactionModel;
use App\Model\Finance\Payment\Payment;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Sales\DeliveryNote\DeliveryNote;

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
        'amount' => 'double',
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
    ];

    public $defaultNumberPrefix = 'INVOICE';

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
        // TODO throw error if customer_id is not provided
        $customerId = $data['customer_id'] ?? null;

        if (! empty($data['delivery_note_ids']) && is_array($data['delivery_note_ids'])) {
            $deliveryNotes = DeliveryNote::joinForm()
                ->active()
                ->notDone()
                ->whereIn(DeliveryNote::getTableName('id'), $data['delivery_note_ids'])
                ->with('form', 'items')
                ->get();

            // TODO check if $deliveryNotes contains at least 1 record and return error if 0 records

            $customerId = $deliveryNotes[0]->customer_id;
        } elseif (! empty($data['sales_order_ids']) && is_array($data['sales_order_ids'])) {
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
        } else {
            $salesInvoice->customer_name = $data['customer_name'];
        }

        $amount = 0;
        $salesInvoiceItems = [];
        $salesInvoiceServices = [];

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (! empty($items) && is_array($items)) {
            $items = array_column($items, null, 'item_id');

            foreach ($deliveryNotes as $deliveryNote) {
                $deliveryNote->form()->update(['done' => true]);

                foreach ($deliveryNote->items as $deliveryNoteItem) {
                    $itemId = $deliveryNoteItem->item_id;
                    $item = $items[$itemId];

                    $price = $item['price'] ?? $item['sell_price'];

                    array_push($salesInvoiceItems, [
                        'delivery_note_id' => $deliveryNoteItem->delivery_note_id,
                        'delivery_note_item_id' => $deliveryNoteItem->id,
                        'item_id' => $itemId,
                        'item_name' => $deliveryNoteItem->item_name,
                        'quantity' => $deliveryNoteItem->quantity,
                        'unit' => $deliveryNoteItem->unit,
                        'converter' => $deliveryNoteItem->converter,
                        'price' => $price,
                        'discount_percent' => $item['discount_percent'] ?? null,
                        'discount_value' => $item['discount_value'] ?? 0,
                        'taxable' => $item['taxable'] ?? 1,
                        'notes' => $item['notes'] ?? null,
                        'allocation_id' => $item['allocation_id'] ?? null,
                    ]);

                    $amount += $deliveryNoteItem->quantity * ($price - ($item['discount_value'] ?? 0));
                }
            }
        }

        // TODO validation services is required only if items is null and must be array
        $services = $data['services'] ?? [];
        if (! empty($services) && is_array($services)) {
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

        if ($data['type_of_tax'] === 'exclude' && ! empty($data['tax'])) {
            $amount += $data['tax'];
        }

        $salesInvoice->amount = $amount;
        $salesInvoice->save();

        $salesInvoice->items()->createMany($salesInvoiceItems);
        $salesInvoice->services()->createMany($salesInvoiceServices);

        $form = new Form;
        $form->fillData($data, $salesInvoice);

        self::updateJournal($salesInvoice);

        return $salesInvoice;
    }

    private static function updateJournal($salesInvoice)
    {
        /**
         * Journal Table
         * -------------------------------------------
         * Account                  | Debit | Credit |
         * -------------------------------------------
         * 1. Account Receivable    |   v   |        | Master Supplier
         * 2. Sales Income          |       |   v    |
         * 3. Inventories           |       |   v    | Master Item
         * 4. Cogs                  |   v   |        |
         * 5. Income Tax Payable    |       |   v    |
         */

        // 1. Account Receivable
        $journal = new Journal;
        $journal->form_id = $salesInvoice->form->id;
        $journal->journalable_type = Customer::class;
        $journal->journalable_id = $salesInvoice->customer_id;
        $journal->chart_of_account_id = ChartOfAccountType::where('name', 'account receivable')->first()->accounts->first()->id;
        $journal->debit = $salesInvoice->amount;
        $journal->save();

        // 2. Sales Income
        $journal = new Journal;
        $journal->form_id = $salesInvoice->form->id;
        $journal->chart_of_account_id = ChartOfAccountType::where('name', 'sales income')->first()->accounts->first()->id;
        $journal->credit = $salesInvoice->amount;
        $journal->save();

        foreach ($salesInvoice->items as $salesItem) {

            $inventory = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
                ->where('item_id', $salesItem->item_id)
                ->whereBetween(Form::getTableName('date'), [$salesInvoice->form->date, $salesInvoice->form->date])
                ->select(Inventory::getTableName('*'))
                ->orderBy(Form::getTableName('date'), 'desc')
                ->with('form')
                ->first();

            $cogs = $inventory->cogs;

            // 3. Inventories
            $journal = new Journal;
            $journal->form_id = $salesInvoice->form->id;
            $journal->journalable_type = Item::class;
            $journal->journalable_id = $salesItem->item_id;
            $journal->chart_of_account_id = ChartOfAccountType::where('name','inventory')->first()->accounts->first()->id;;
            $journal->credit = $cogs;
            $journal->save();

            // 4. Cogs
            $journal = new Journal;
            $journal->form_id = $salesInvoice->form->id;
            $journal->journalable_type = Item::class;
            $journal->journalable_id = $salesItem->item_id;
            $journal->chart_of_account_id = ChartOfAccountType::where('name','inventory')->first()->accounts->first()->id;;
            $journal->credit = $cogs;
            $journal->save();
        }

        // 5. Income Tax Payable
        $journal = new Journal;
        $journal->form_id = $salesInvoice->form->id;
        $journal->chart_of_account_id = ChartOfAccountType::where('name','other current liability')->first()->accounts->first()->id;;
        $journal->credit = $salesInvoice->tax;
        $journal->save();
    }
}
