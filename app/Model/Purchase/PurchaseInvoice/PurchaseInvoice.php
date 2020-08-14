<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Exceptions\IsReferencedException;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Traits\Model\Purchase\PurchaseInvoiceJoin;
use App\Traits\Model\Purchase\PurchaseInvoiceRelation;
use Carbon\Carbon;

class PurchaseInvoice extends TransactionModel
{
    use PurchaseInvoiceJoin, PurchaseInvoiceRelation;

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

    public function updateStatus()
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

        $purchaseInvoice->amount = self::calculateAmount($purchaseInvoice, $items);
        $purchaseInvoice->remaining = $purchaseInvoice->amount;

        $purchaseInvoice->save();

        $purchaseInvoice->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $purchaseInvoice);

        // updated to done if the amount is 0 because of down payment
        $purchaseInvoice->updateStatus();

        self::setPurchaseReceiveDone($purchaseInvoice);

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

    private static function calculateAmount($purchaseInvoice, $items)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * ($item->price - $item->discount_value);
        }, 0);


        $amount -= $purchaseInvoice->discount_value;
        $amount += $purchaseInvoice->delivery_fee;
        $amount += $purchaseInvoice->type_of_tax === 'exclude' ? $purchaseInvoice->tax : 0;

        return $amount;
    }

    private static function setPurchaseReceiveDone($purchaseInvoice)
    {
        foreach ($purchaseInvoice->items as $purchaseInvoiceItem) {
            $purchaseInvoiceItem->purchaseReceive->form->done = true;
            $purchaseInvoiceItem->purchaseReceive->form->save();
        }
    }

    /**
     * Update price, cogs in inventory.
     *
     * @param $form
     * @param $purchaseInvoice
     */
    public static function updateInventory($form, $purchaseInvoice)
    {
        foreach ($purchaseInvoice->items as $item) {
            if ($item->quantity > 0) {
                $options = [];
                if ($item->item->require_expiry_date) {
                    $options['expiry_date'] = $item->purchaseReceiveItem->expiry_date;
                }
                if ($item->item->require_production_number) {
                    $options['production_number'] = $item->purchaseReceiveItem->production_number;
                }

                $options['quantity_reference'] = $item->quantity;
                $options['unit_reference'] = $item->unit;
                $options['converter_reference'] = $item->converter;
                InventoryHelper::increase($form, $item->purchaseReceive->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
            }
        }
    }

    private static function calculatePrice($item, $total, $additionalFee)
    {
        $totalPerItem = ($item->price - $item->discount_value) * $item->quantity * $item->converter;
        $feePerItem = $totalPerItem / $total * $additionalFee;

        return ($totalPerItem + $feePerItem) / $item->quantity;
    }

    public static function updateJournal($purchaseInvoice)
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
