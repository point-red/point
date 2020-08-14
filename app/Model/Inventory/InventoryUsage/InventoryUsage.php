<?php

namespace App\Model\Inventory\InventoryUsage;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
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

        $inventoryUsage->updateInventory($form, $inventoryUsage);

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

    private static function mapItems($items)
    {
        $array = [];
        foreach ($items as $item) {
            $itemModel = Item::find($item['item_id']);
            if ($itemModel->require_production_number || $itemModel->require_expiry_date) {
                if ($item['dna']) {
                    foreach ($item['dna'] as $dna) {
                        $dnaItem = $item;
                        $dnaItem['quantity'] = $dna['quantity'];
                        $dnaItem['production_number'] = $dna['production_number'];
                        $dnaItem['expiry_date'] = $dna['expiry_date'];
                        array_push($array, $dnaItem);
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
