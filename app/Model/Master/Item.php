<?php

namespace App\Model\Master;

use App\Model\Form;
use App\Model\MasterModel;
use App\Model\Inventory\Inventory;
use App\Model\Accounting\ChartOfAccount;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Inventory\OpeningStock\OpeningStock;
use App\Model\Inventory\OpeningStock\OpeningStockWarehouse;

class Item extends MasterModel
{
    public static $morphName = 'Item';

    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'chart_of_account_id',
        'barcode',
        'notes',
        'size',
        'color',
        'weight',
        'stock_reminder',
        'disabled',
    ];

    protected $casts = [
        'stock' => 'double',
        'stock_reminder' => 'double',
        'cogs' => 'double',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /**
     * Get all of the units for the items.
     */
    public function units()
    {
        return $this->hasMany(ItemUnit::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public static function create($data)
    {
        $item = new self;
        $item->fill($data);
        $item->save();

        $units = $data['units'];
        $unitsToBeInserted = [];
        foreach ($units as $unit) {
            $itemUnit = new ItemUnit();
            $itemUnit->fill($unit);
            array_push($unitsToBeInserted, $itemUnit);
        }
        $item->units()->saveMany($unitsToBeInserted);

        if (isset($data['opening_stocks'])) {
            $openingStock = new OpeningStock;
            $openingStock->item_id = $item->id;
            $openingStock->save();

            $form = new Form;
            $form->saveData([
                'date' => now(),
                'increment_group' => date('Ym'),
            ], $openingStock);
            $form->save();

            foreach ($data['opening_stocks'] as $osWarehouse) {
                if ($osWarehouse['warehouse_id'] != null && $osWarehouse['quantity'] != null && $osWarehouse['price'] != null) {
                    $openingStockWarehouse = new OpeningStockWarehouse;
                    $openingStockWarehouse->opening_stock_id = $openingStock->id;
                    $openingStockWarehouse->warehouse_id = $osWarehouse['warehouse_id'];
                    $openingStockWarehouse->quantity = $osWarehouse['quantity'];
                    $openingStockWarehouse->price = $osWarehouse['price'];
                    $openingStockWarehouse->save();

                    InventoryHelper::increase($form->id, $osWarehouse['warehouse_id'], $item->id, $osWarehouse['quantity'], $osWarehouse['price']);
                }
            }
        }

        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                if (!$group['id'] && $group['name']) {
                    $newGroup = new Group;
                    $newGroup->name = $group['name'];
                    $newGroup->type = $group['type'];
                    $newGroup->class_reference = $group['class_reference'];
                    $item->groups()->attach($newGroup->id);
                } elseif ($group['id']) {
                    $item->groups()->attach($group['id']);
                }
            }
        }

        return $item;
    }
}
