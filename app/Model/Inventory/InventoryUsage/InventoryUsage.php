<?php

namespace App\Model\Inventory\InventoryUsage;

use App\Model\Form;
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

        return $inventoryUsage;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $inventoryUsageItem = new InventoryUsageItem;
            $inventoryUsageItem->fill($item);

            return $inventoryUsageItem;
        }, $items);
    }
}
