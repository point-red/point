<?php

namespace App\Model\Inventory\InventoryUsage;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class InventoryUsage extends TransactionModel
{
    public static $morphName = 'InventoryUsage';

    protected $connection = 'tenant';

    public static $alias = 'inventory_usage';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'employee_id'
    ];

    public $defaultNumberPrefix = 'IU';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(InventoryUsageItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
    }

    public static function create($data)
    {
        $inventoryUsage = new self;
        $inventoryUsage->fill($data);
        $inventoryUsage->save();

        $items = self::mapItems($data['items'] ?? []);
        $inventoryUsage->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $inventoryUsage);

        return $inventoryUsage;
    }


    /**
     * Update price, cogs in inventory.
     *
     * @param $form
     * @param $inventoryUsage
     */
    public function updateInventory($form, $inventoryUsage)
    {
        foreach ($inventoryUsage->items as $item) {
            if ($item->quantity > 0) {
                $options = [];
                if ($item->item->require_expiry_date) {
                    $options['expiry_date'] = $item->expiry_date;
                }
                if ($item->item->require_production_number) {
                    $options['production_number'] = $item->production_number;
                }

                $options['quantity_reference'] = $item->quantity;
                $options['unit_reference'] = $item->unit;
                $options['converter_reference'] = $item->converter;
                InventoryHelper::decrease($form, $item->inventoryUsage->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
            }
        }
    }

    public static function checkIsJournalBalance($usage)
    {
        $valueDebit = Journal::where("form_id", $usage->form->id)
            ->where("journalable_type", Item::$morphName)
            ->sum("debit");
        $valueCredit = Journal::where("form_id", $usage->form->id)
            ->where("journalable_type", Item::$morphName)
            ->sum("credit");
        if ($valueDebit - $valueCredit !== 0) {
            throw new Exception('journal entry is not balanced', 422);
        }
    }

    public static function updateJournal($usage)
    {
        foreach ($usage->items as $usageItem) {
            $amount = $usageItem->item->cogs($usageItem->item_id) * $usageItem->quantity;

            $journal = new Journal;
            $journal->form_id = $usage->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $usageItem->item_id;
            $journal->chart_of_account_id = $usageItem->item->chart_of_account_id;
            $journal->debit = $amount;
            $journal->save();

            $journal = new Journal;
            $journal->form_id = $usage->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $usageItem->item_id;
            $journal->chart_of_account_id = get_setting_journal('inventory usage', 'difference stock expense');
            $journal->credit = $amount;
            $journal->save();
        }

        self::checkIsJournalBalance($usage);
    }

    private static function mapItems($items)
    {
        $array = [];
        foreach ($items as $item) {
            $itemModel = Item::find($item['item_id']);
            if ($itemModel->require_production_number || $itemModel->require_expiry_date) {
                if ($item['dna']) {
                    foreach ($item['dna'] as $dna) {
                        if ($dna['quantity'] > 0) {
                            $dnaItem = $item;
                            $dnaItem['quantity'] = $dna['quantity'];
                            $dnaItem['production_number'] = $dna['production_number'];
                            $dnaItem['expiry_date'] = $dna['expiry_date'];
                            array_push($array, $dnaItem);
                        }
                    }
                }
            } else {
                array_push($array, $item);
            }
        }
        return array_map(function ($item) {
            $inventoryUsageItem = new InventoryUsageItem();
            $inventoryUsageItem->fill($item);
            return $inventoryUsageItem;
        }, $array);
    }
}
