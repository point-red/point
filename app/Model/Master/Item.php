<?php

namespace App\Model\Master;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Inventory\OpeningStock\OpeningStock;
use App\Model\Inventory\OpeningStock\OpeningStockWarehouse;
use App\Model\MasterModel;

class Item extends MasterModel
{
    public static $morphName = 'Item';

    protected $connection = 'tenant';

    protected $appends = ['label'];

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
        'require_expiry_date',
        'require_production_number',
    ];

    protected $casts = [
        'stock' => 'double',
        'stock_reminder' => 'double',
        'cogs' => 'double',
    ];

    public function getLabelAttribute()
    {
        $label = $this->code ? '[' . $this->number . '] ' : '';

        return $label . $this->name;
    }

    /**
     * Get all of the item's journals.
     */
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->belongsToMany(ItemGroup::class);
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
        $unitDefault = '';
        foreach ($units as $key => $unit) {
            if ($unit['converter'] <= 0 || $unit['name'] == '') {
                continue;
            }

            $itemUnit = new ItemUnit();
            $itemUnit->item_id = $item->id;
            $itemUnit->fill($unit);
            $itemUnit->save();

            if ($key == 0) {
                $item->unit_default = $itemUnit->id;
                $unitDefault = $itemUnit->label;
                $item->save();
            }

            if ($unit['default_purchase'] == true) {
                $item->unit_default_purchase = $itemUnit->id;
                $item->save();
            }

            if ($unit['default_sales'] == true) {
                $item->unit_default_sales = $itemUnit->id;
                $item->save();
            }
        }

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
                    $options = [];
                    if (array_key_exists('expiry_date', $osWarehouse)) {
                        $openingStockWarehouse->expiry_date = $osWarehouse['expiry_date'];
                        $options['expiry_date'] = $openingStockWarehouse->expiry_date;
                    }
                    if (array_key_exists('production_number', $osWarehouse)) {
                        $openingStockWarehouse->production_number = $osWarehouse['production_number'];
                        $options['production_number'] = $openingStockWarehouse->production_number;
                    }
                    $options['quantity_reference'] = $openingStockWarehouse->quantity;
                    $options['unit_reference'] = $unitDefault;
                    $options['converter_reference'] = 1;
                    $openingStockWarehouse->save();

                    InventoryHelper::increase($form, Warehouse::find($osWarehouse['warehouse_id']), $item->id, $unitDefault, 1, $osWarehouse['quantity'], $options);
                }
            }
        }

        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                if (! $group['id'] && $group['name']) {
                    $newGroup = new Group;
                    $newGroup->name = $group['name'];
                    $newGroup->save();
                    $item->groups()->syncWithoutDetaching($newGroup->id);
                } elseif ($group['id']) {
                    $item->groups()->syncWithoutDetaching($group['id']);
                }
            }
        }

        return $item;
    }
}
