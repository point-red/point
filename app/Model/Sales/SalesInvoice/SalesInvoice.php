<?php

namespace App\Model\Sales\SalesInvoice;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Finance\Payment\Payment;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;

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
        'paid' => 'double',
        'remaining' => 'double',
    ];

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

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

    public function downPayments()
    {
        return $this->belongsToMany(SalesDownPayment::class, 'down_payment_invoice', 'down_payment_id', 'invoice_id');
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

    public function detachDownPayments()
    {
        $this->downPayments()->detach();

        $downPayments = $this->downPayments;
        $downPaymentIds = $downPayments->pluck('id');
        Form::whereIn('referenceable_id', $downPaymentIds)
            ->where('referenceable_type', SalesDownPayment::class)
            ->update(['done' => false]);
    }

    public function updateIfDone()
    {
        if ($this->remaining <= 0) {
            $this->form()->update(['done' => true]);
        }
    }

    public static function create($data)
    {
        $salesInvoice = new self;
        $salesInvoice->fill($data);

        /* Items should have delivery notes */
        if (! empty($data['delivery_note_ids'])) {
            $deliveryNotes = self::setDeliveryNotes($data['delivery_note_ids']);
            $customerId = $deliveryNotes[0]->customer_id;
        }
        /* Services doesn't have delivery notes, get data from sales order instead */
        elseif (! empty($data['sales_order_ids'])) {
            $salesOrders = self::setSalesOrders($data['sales_order_ids']);
            $customerId = $salesOrders[0]->customer_id;
        }

        // TODO throw error if $customerId is null or invalid id
        $salesInvoice->customer_id = $customerId;
        $salesInvoice->customer_name = self::getCustomerName($salesInvoice);

        $salesInvoiceItems = self::getItems($data['items'] ?? [], $deliveryNotes ?? []);
        $salesInvoiceServices = self::getServices($data['services'] ?? [], $salesOrders ?? []);

        $salesInvoice->amount = self::getAmounts($salesInvoice, $salesInvoiceItems, $salesInvoiceServices);

        $totalDownPayments = self::getTotalDownPayments($data['down_payments'] ?? []);
        $salesInvoice->remaining = $salesInvoice->amount - $totalDownPayments;

        $salesInvoice->save();

        $salesInvoice->items()->createMany($salesInvoiceItems);
        $salesInvoice->services()->createMany($salesInvoiceServices);
        $salesInvoice->downPayments()->attach(array_column($data['down_payments'] ?? [], 'amount', 'id'));

        $form = new Form;
        $form->fillData($data, $salesInvoice);

        self::setDeliveryNotesDone($data['delivery_note_ids'] ?? []);
        self::setSalesOrdersDone($data['sales_order_ids'] ?? []);
        self::setDownPaymentsDone($data['down_payments'] ?? []);
        self::updateJournal($salesInvoice);

        return $salesInvoice;
    }

    private static function setDeliveryNotes($deliveryNoteIds)
    {
        $deliveryNotes = DeliveryNote::joinForm()
            ->active()
            ->notDone()
            ->whereIn(DeliveryNote::getTableName('id'), $deliveryNoteIds)
            ->with('form', 'items')
            ->get();

        if ($deliveryNotes->isEmpty()) {
            return response()->json([
                'code' => 422,
                'message' => 'Delivery Notes not found.',
            ], 422);
        }

        return $deliveryNotes;
    }

    private static function setSalesOrders($salesOrderIds)
    {
        $salesOrders = SalesOrder::joinForm()
            ->active()
            ->notDone()
            ->whereIn(SalesOrder::getTableName('id'), $salesOrderIds)
            ->with('form', 'services')
            ->get();

        if ($salesOrders->isEmpty()) {
            return response()->json([
                'code' => 422,
                'message' => 'Sales Orders not found.',
            ], 422);
        }

        return $salesOrders;
    }

    private static function getCustomerName($salesInvoice)
    {
        if (empty($salesInvoice->customer_name)) {
            $customer = Customer::findOrFail($salesInvoice->customer->id, ['name']);

            return $customer->name;
        }

        return $salesInvoice->customer_name;
    }

    private static function getItems($items, $deliveryNotes)
    {
        if (empty($items)) {
            return [];
        }

        $items = array_column($items, null, 'item_id');

        $salesInvoiceItems = [];

        foreach ($deliveryNotes as $deliveryNote) {
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
            }
        }

        return $salesInvoiceItems;
    }

    private static function getServices($services, $salesOrders)
    {
        if (! empty($services) && is_array($services)) {
            return [];
        }

        $services = array_column($services, null, 'service_id');

        $salesInvoiceServices = [];

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
            }
        }

        return $salesInvoiceServices;
    }

    private static function getTotalDownPayments($downPayments)
    {
        return array_reduce($downPayments, function ($carry, $downPayment) {
            return $carry + $downPayment['amount'];
        }, 0);
    }

    private static function getAmounts($salesInvoice, $items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            $price = $item['quantity'] * ($item['price'] - $item['discount_value']);

            return $carry + $price;
        }, 0);

        $amount += array_reduce($services, function ($carry, $service) {
            $price = $service['quantity'] * ($service['price'] - $service['discount_value']);

            return $carry + $price;
        }, 0);

        $amount -= $salesInvoice->discount_value;
        $amount += $salesInvoice->delivery_fee;
        $amount += $salesInvoice->type_of_tax === 'exclude' ? $salesInvoice->tax : 0;

        return $amount;
    }

    private static function setDeliveryNotesDone($deliveryNoteIds)
    {
        if (! empty($deliveryNoteIds)) {
            $affectedRows = Form::where('formable_type', DeliveryNote::class)
                ->whereIn('formable_id', $deliveryNoteIds)
                ->update(['done' => true]);
        }

        // TODO do something if $affectedRows === 0 or different than count($deliveryNoteIds)
    }

    private static function setSalesOrdersDone($salesOrderIds)
    {
        if (! empty($salesOrderIds)) {
            $affectedRows = Form::where('formable_type', SalesOrder::class)
                ->whereIn('formable_id', $salesOrderIds)
                ->update(['done' => true]);
        }

        // TODO do something if $affectedRows === 0 or different than count($salesOrderIds)
    }

    private static function setDownPaymentsDone($downPayments)
    {
        foreach ($downPayments as $downPayment) {
            $salesDownPayment = SalesDownPayment::findOrFail($downPayment['id']);
            $salesDownPayment->updateIfDone();
        }
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
         * 5. Income Tax Payable    |       |   v    |.
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
                ->where(Form::getTableName('date'), '<=', $salesInvoice->form->date)
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
            $journal->chart_of_account_id = ChartOfAccountType::where('name', 'inventory')->first()->accounts->first()->id;
            $journal->credit = $cogs;
            $journal->save();

            // 4. Cogs
            $journal = new Journal;
            $journal->form_id = $salesInvoice->form->id;
            $journal->journalable_type = Item::class;
            $journal->journalable_id = $salesItem->item_id;
            $journal->chart_of_account_id = ChartOfAccountType::where('name', 'inventory')->first()->accounts->first()->id;
            $journal->credit = $cogs;
            $journal->save();
        }

        // 5. Income Tax Payable
        $journal = new Journal;
        $journal->form_id = $salesInvoice->form->id;
        $journal->chart_of_account_id = ChartOfAccountType::where('name', 'other current liability')->first()->accounts->first()->id;
        $journal->credit = $salesInvoice->tax;
        $journal->save();
    }
}
