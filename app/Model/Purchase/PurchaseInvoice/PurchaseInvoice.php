<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use Carbon\Carbon;

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
        'supplier_name',
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

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public $defaultNumberPrefix = 'PI';

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
        if (! empty($items) && is_array($items)) {
            $items = array_column($items, null, 'item_id');
        }

        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (! empty($services) && is_array($services)) {
            $services = array_column($services, null, 'service_id');
        }

        foreach ($purchaseReceives as $purchaseReceive) {
            $purchaseReceive->form()->update(['done' => true]);

            foreach ($purchaseReceive->items as $purchaseReceiveItem) {
                $itemId = $purchaseReceiveItem->item_id;
                $item = $items[$itemId];
                $price = $item['price'] ?? $item['purchase_price'];

                array_push($purchaseInvoiceItems, [
                    'purchase_receive_id' => $purchaseReceiveItem->purchase_receive_id,
                    'purchase_receive_item_id' => $purchaseReceiveItem->id,
                    'item_id' => $itemId,
                    'item_name' => $purchaseReceiveItem->item_name,
                    'quantity' => $purchaseReceiveItem->quantity,
                    'unit' => $purchaseReceiveItem->unit,
                    'converter' => $purchaseReceiveItem->converter,
                    'price' => $price,
                    'discount_percent' => $item['discount_percent'] ?? null,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'taxable' => $item['taxable'] ?? 1,
                    'notes' => $item['notes'] ?? null,
                    'allocation_id' => $item['allocation_id'] ?? null,
                ]);

                $amount += $purchaseReceiveItem->quantity * ($price - ($item['discount_value'] ?? 0));
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

        if ($data['type_of_tax'] === 'exclude' && ! empty($data['tax'])) {
            $amount += $data['tax'];
        }

        $purchaseInvoice->amount = $amount;
        $purchaseInvoice->save();

        $purchaseInvoice->items()->createMany($purchaseInvoiceItems);
        $purchaseInvoice->services()->createMany($purchaseInvoiceServices);

        $form = new Form;
        $form->fillData($data, $purchaseInvoice);

        self::updateInventory($purchaseInvoice, $purchaseReceives);
        self::updateJournal($purchaseInvoice);

        return $purchaseInvoice;
    }

    private static function updateInventory($purchaseInvoice, $purchaseReceives)
    {
        // TODO: update value if different from order
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
        $journal->journalable_type = Supplier::class;
        $journal->journalable_id = $purchaseInvoice->supplier_id;
        $journal->chart_of_account_id = ChartOfAccountType::where('name', 'current liability')->first()->accounts->first()->id;
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
            $journal->journalable_type = Item::class;
            $journal->journalable_id = $purchaseItem->item_id;
            $journal->chart_of_account_id = ChartOfAccountType::where('name', 'inventory')->first()->accounts->first()->id;
            $journal->debit = $itemAmount;
            $journal->save();
        }

        // 3. Income Tax Receivable
        $journal = new Journal;
        $journal->form_id = $purchaseInvoice->form->id;
        $journal->chart_of_account_id = ChartOfAccountType::where('name', 'other account receivable')->first()->accounts->first()->id;
        $journal->debit = $purchaseInvoice->tax;
        $journal->save();
    }
}
