<?php

namespace App\Model\Master;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccount;
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
        'require_production_number',
        'require_expiry_date',
    ];

    protected $casts = [
        'stock' => 'double',
        'stock_reminder' => 'double',
        'cogs' => 'double',
    ];

    public function getLabelAttribute()
    {
        return $this->code . ' - ' . $this->name;
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
                $item->save();
            }

            if ($unit['default_purchase'] == true) {
                $item->unit_default_purchase = $itemUnit->id;
                $item->save();
            } else if ($unit['default_sales'] == true) {
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
                    if (array_key_exists('production_number', $osWarehouse)) {
                        $openingStockWarehouse->production_number = $osWarehouse['production_number'];
                    }
                    if (array_key_exists('expiry_date', $osWarehouse)) {
                        $openingStockWarehouse->expiry_date = convert_to_server_timezone($osWarehouse['expiry_date']);
                    }
                    $openingStockWarehouse->price = $osWarehouse['price'];
                    $openingStockWarehouse->save();

                    InventoryHelper::increase($form->id, $osWarehouse['warehouse_id'], $item->id, $osWarehouse['quantity'], $osWarehouse['price']);
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
