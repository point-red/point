<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\AllocationReport;
use App\Model\Master\Allocation;
use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Finance\Payment\Payment;
use App\Exceptions\IsReferencedException;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;

class SalesInvoice extends TransactionModel
{
    public static $morphName = 'SalesInvoice';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
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

    public $defaultNumberPrefix = 'INVOICE';

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

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
        return $this->belongsToMany(SalesDownPayment::class, 'sales_down_payment_invoice', 'invoice_id', 'down_payment_id');
    }

    /**
     * Get the invoice's payment.
     */
    public function payments()
    {
        return $this->morphToMany(Payment::class, 'referenceable', 'payment_details')->active();
    }

    public function detachDownPayments()
    {
        $this->downPayments()->detach();

        $downPayments = $this->downPayments;
        $downPaymentIds = $downPayments->pluck('id');
        Form::whereIn('formable_id', $downPaymentIds)
            ->where('formable_type', SalesDownPayment::$morphName)
            ->update(['done' => false]);
    }

    public function updateIfDone()
    {
        $done = $this->remaining <= 0;
        $this->form()->update(['done' => $done]);
    }

    public function isAllowedToUpdate()
    {
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by payments
        if ($this->payments->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by payments', $this->payments);
        }
    }

    public static function create($data)
    {
        $salesInvoice = new self;
        $salesInvoice->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $salesInvoice->amount = self::calculateAmount($salesInvoice, $items, $services);

        $totalDownPayments = self::getTotalDownPayments($data['down_payments'] ?? []);
        $salesInvoice->remaining = $salesInvoice->amount - $totalDownPayments;

        $salesInvoice->save();

        $salesInvoice->items()->saveMany($items);
        $salesInvoice->services()->saveMany($services);
        $salesInvoice->downPayments()->attach(array_column($data['down_payments'] ?? [], null, 'id'));

        $form = new Form;
        $form->saveData($data, $salesInvoice);

        // updated to done if the amount is 0 because of down payment
        $salesInvoice->updateIfDone();

        self::setDeliveryNotesDone($salesInvoice);
        self::setSalesOrdersDone($salesInvoice);
        self::setDownPaymentsDone($data['down_payments'] ?? []);
        self::updateJournal($salesInvoice);
        self::setAllocationReport($salesInvoice);

        return $salesInvoice;
    }

    private static function setAllocationReport($salesInvoice)
    {
        foreach ($salesInvoice->items as $salesInvoiceItem) {
            if ($salesInvoiceItem->allocation_id != null) {
                $allocationReport = new AllocationReport;
                $allocationReport->allocation_id = $salesInvoiceItem->allocation_id;
                $allocationReport->allocationable_id= $salesInvoiceItem->id;
                $allocationReport->allocationable_type = SalesInvoiceItem::class;
                $allocationReport->notes = $salesInvoiceItem->notes;
                $allocationReport->form_id = $salesInvoiceItem->salesInvoice->form->id;
                $allocationReport->save();
            }
        }

        foreach ($salesInvoice->services as $salesInvoiceService) {
            if ($salesInvoiceService->allocation_id != null) {
                $allocationReport = new AllocationReport;
                $allocationReport->allocation_id = $salesInvoiceService->allocation_id;
                $allocationReport->allocationable_id= $salesInvoiceService->id;
                $allocationReport->allocationable_type = SalesInvoiceService::class;
                $allocationReport->notes = $salesInvoiceService->notes;
                $allocationReport->form_id = $salesInvoiceService->salesInvoice->form->id;
                $allocationReport->save();
            }
        }
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $salesInvoiceItem = new SalesInvoiceItem;
            $salesInvoiceItem->fill($item);

            if ($item['allocation_name']) {
                $salesInvoiceItem['allocation_id'] = Allocation::firstOrCreate([
                   'code' => $item['allocation_code'],
                   'name' => $item['allocation_name'],
                ])->id;
            }

            return $salesInvoiceItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $salesInvoiceService = new SalesInvoiceService;
            $salesInvoiceService->fill($service);

            if ($service->allocation_name) {
                $salesInvoiceService->allocation_id = Allocation::firstOrCreate([
                    'code' => $service->allocation_code,
                    'name' => $service->allocation_name,
                ])->id;
            }

            return $salesInvoiceService;
        }, $services);
    }

    private static function getTotalDownPayments($downPayments)
    {
        return array_reduce($downPayments, function ($carry, $downPayment) {
            return $carry + $downPayment['amount'];
        }, 0);
    }

    private static function calculateAmount($salesInvoice, $items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * ($item->price - $item->discount_value);
        }, 0);

        $amount += array_reduce($services, function ($carry, $service) {
            return $carry + $service['quantity'] * ($service['price'] - $service['discount_value']);
        }, 0);

        $amount -= $salesInvoice->discount_value;
        $amount += $salesInvoice->delivery_fee;
        $amount += $salesInvoice->type_of_tax === 'exclude' ? $salesInvoice->tax : 0;

        return $amount;
    }

    public static function setDeliveryNotesDone($salesInvoice)
    {
        if ($salesInvoice->items->count()) {
            $deliveryNoteIds = $salesInvoice->items()->groupBy('delivery_note_id')->pluck('delivery_note_id');

            $affectedRows = Form::where('formable_type', DeliveryNote::$morphName)
                ->whereIn('formable_id', $deliveryNoteIds)
                ->update(['done' => true]);
            // TODO do something if $affectedRows === 0 or different than count($deliveryNoteIds)
        }
    }

    private static function setSalesOrdersDone($salesInvoice)
    {
        if ($salesInvoice->services->count()) {
            $salesOrderIds = $salesInvoice->services()->groupBy('sales_order_id')->pluck('sales_order_id');

            $affectedRows = Form::where('formable_type', SalesOrder::$morphName)
                ->whereIn('formable_id', $salesOrderIds)
                ->update(['done' => true]);
            // TODO do something if $affectedRows === 0 or different than count($salesOrderIds)
        }
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
        $journal->journalable_type = Customer::$morphName;
        $journal->journalable_id = $salesInvoice->customer_id;
        $journal->chart_of_account_id = get_setting_journal('sales', 'account receivable');
        $journal->debit = $salesInvoice->amount;
        $journal->save();

        // 2. Sales Income
        $journal = new Journal;
        $journal->form_id = $salesInvoice->form->id;
        $journal->chart_of_account_id = get_setting_journal('sales', 'sales income');
        $journal->credit = $salesInvoice->amount - $salesInvoice->tax;
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
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $salesItem->item_id;
            $journal->chart_of_account_id = $salesItem->item->chart_of_account_id;
            $journal->credit = $cogs * $salesItem->quantity;
            $journal->save();

            // 4. Cogs
            $journal = new Journal;
            $journal->form_id = $salesInvoice->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $salesItem->item_id;
            $journal->chart_of_account_id = get_setting_journal('sales', 'cost of sales');
            $journal->debit = $cogs * $salesItem->quantity;
            $journal->save();
        }

        // 5. Income Tax Payable
        $journal = new Journal;
        $journal->form_id = $salesInvoice->form->id;
        $journal->chart_of_account_id = get_setting_journal('sales', 'income tax payable');
        $journal->credit = $salesInvoice->tax;
        $journal->save();
    }
}
