<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Exceptions\IsReferencedException;
use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\TransactionModel;
use Carbon\Carbon;

class PurchaseInvoice extends TransactionModel
{
    public static $morphName = 'PurchaseInvoice';

    protected $connection = 'tenant';

    public static $alias = 'purchase_invoice';

    public $timestamps = false;

    protected $fillable = [
        'due_date',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
        'supplier_id',
        'supplier_name',
        'supplier_address',
        'supplier_phone',
        'invoice_number',
    ];

    protected $casts = [
        'amount' => 'double',
        'tax' => 'double',
        'delivery_fee' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'paid' => 'double',
        'remaining' => 'double',
    ];

    public $defaultNumberPrefix = 'PI';

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

    public function purchaseReceives()
    {
        return $this->belongsToMany(PurchaseReceive::class, 'purchase_invoice_items')->distinct();
    }

    public function downPayments()
    {
        return $this->belongsToMany(PurchaseDownPayment::class, 'down_payment_invoice', 'down_payment_id', 'invoice_id');
    }

    public function payments()
    {
        return $this->morphToMany(Payment::class, 'referenceable', 'payment_details')->active();
    }

    // public function purchase

    public function updateIfDone()
    {
        $done = $this->remaining <= 0;
        $this->form()->update(['done' => $done]);
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    /**
     * @throws IsReferencedException
     */
    private function isNotReferenced()
    {
        // Check if not referenced by payments
        if ($this->payments->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by payments', $this->payments);
        }
    }

    public static function create($data)
    {
        $purchaseInvoice = new self;
        $purchaseInvoice->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $purchaseInvoice->amount = self::calculateAmount($purchaseInvoice, $items, $services);
        $purchaseInvoice->remaining = $purchaseInvoice->amount;

        $purchaseInvoice->save();

        $purchaseInvoice->items()->saveMany($items);
        $purchaseInvoice->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $purchaseInvoice);

        // updated to done if the amount is 0 because of down payment
        $purchaseInvoice->updateIfDone();

        self::setPurchaseReceiveDone($purchaseInvoice);
        self::updateInventory($purchaseInvoice);
        self::updateJournal($purchaseInvoice);

        return $purchaseInvoice;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $purchaseInvoiceItem = new PurchaseInvoiceItem;
            $purchaseInvoiceItem->fill($item);

            return $purchaseInvoiceItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $purchaseInvoiceService = new PurchaseInvoiceService;
            $purchaseInvoiceService->fill($service);

            return $purchaseInvoiceService;
        }, $services);
    }

    private static function calculateAmount($purchaseInvoice, $items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * $item->converter * ($item->price - $item->discount_value);
        }, 0);

        $amount += array_reduce($services, function ($carry, $service) {
            return $carry + $service->quantity * ($service->price - $service->discount_value);
        }, 0);

        $amount -= $purchaseInvoice->discount_value;
        $amount += $purchaseInvoice->delivery_fee;
        $amount += $purchaseInvoice->type_of_tax === 'exclude' ? $purchaseInvoice->tax : 0;

        return $amount;
    }

    private static function setPurchaseReceiveDone($purchaseInvoice)
    {
        $purchaseReceives = $purchaseInvoice->purchaseReceives;
        foreach ($purchaseReceives as $receive) {
            $receive->form()->update(['done' => true]);
        }
    }

    /**
     * Update price, cogs in inventory.
     */
    private static function updateInventory($purchaseInvoice)
    {
        $purchaseInvoice->load('items.purchaseReceive.form');
        $items = $purchaseInvoice->items;

        foreach ($items as $item) {
            $formId = $item->purchaseReceive->form->id;
            $itemId = $item->item_id;

            $additionalFee = $purchaseInvoice->delivery_fee - $purchaseInvoice->discount_value;
            $total = $purchaseInvoice->amount - $additionalFee - $purchaseInvoice->tax;
            $price = self::calculatePrice($item, $total, $additionalFee);

            $inventory = Inventory::where('form_id', $formId)->where('item_id', $itemId)->first();

            $inventory->price = $price;
            $inventory->total_value = $price * $inventory->quantity;
            $inventory->cogs = $inventory->total_value / $inventory->total_quantity;
            $inventory->save();
        }
    }

    private static function calculatePrice($item, $total, $additionalFee)
    {
        $totalPerItem = ($item->price - $item->discount_value) * $item->quantity * $item->converter;
        $feePerItem = $totalPerItem / $total * $additionalFee;

        return ($totalPerItem + $feePerItem) / $item->quantity;
    }

    private static function updateJournal($purchaseInvoice)
    {
        /**
         * Journal Table
         * -------------------------------------------
         * Account                  | Debit | Credit |
         * -------------------------------------------
         * 1. Account Payable       |       |   v    | Master Supplier
         * 2. Inventories           |   v   |        | Master Item
         * 3. Income Tax Receivable |   v   |        |.
         */

        // 1. Account Payable
        $journal = new Journal;
        $journal->form_id = $purchaseInvoice->form->id;
        $journal->journalable_type = Supplier::$morphName;
        $journal->journalable_id = $purchaseInvoice->supplier_id;
        $journal->chart_of_account_id = get_setting_journal('purchase', 'account payable');
        $journal->credit = $purchaseInvoice->amount;
        $journal->save();

        $totalItemsAmount = $purchaseInvoice->items->reduce(function ($carry, $item) {
            return $carry + $item->quantity * ($item->price - $item->discount_value);
        }, 0);

        foreach ($purchaseInvoice->items as $purchaseItem) {
            $itemAmount = ($purchaseItem->price - $purchaseItem->discount_value) * $purchaseItem->quantity;
            $itemAmountPercentage = $itemAmount / $totalItemsAmount;
            // Add global discount
            $itemAmount -= $itemAmountPercentage * $purchaseInvoice->discount_value;
            // Add Delivery Fee
            $itemAmount += $itemAmountPercentage * $purchaseInvoice->delivery_fee;

            if ($purchaseInvoice->type_of_tax == 'include') {
                // Remove tax from item
                $itemAmount -= $itemAmountPercentage * $purchaseInvoice->tax;
            }

            // 2. Inventories
            $journal = new Journal;
            $journal->form_id = $purchaseInvoice->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $purchaseItem->item_id;
            $journal->chart_of_account_id = $purchaseItem->item->chart_of_account_id;
            $journal->debit = $itemAmount;
            $journal->save();
        }

        // 3. Income Tax Receivable
        $journal = new Journal;
        $journal->form_id = $purchaseInvoice->form->id;
        $journal->chart_of_account_id = get_setting_journal('purchase', 'income tax receivable');
        $journal->debit = $purchaseInvoice->tax;
        $journal->save();
    }
}
