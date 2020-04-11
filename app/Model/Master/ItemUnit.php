<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\ItemUnitJoin;
use App\Traits\Model\Master\ItemUnitRelation;

class ItemUnit extends MasterModel
{
    use ItemUnitRelation, ItemUnitJoin;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'label',
        'converter',
        'item_id',
    ];

    protected $casts = [
        'converter' => 'double',
    ];

    public static $alias = 'item_unit';

    /**
     * Get the price for this unit.
     */
    public function prices()
    {
        return $this
            ->belongsToMany(PricingGroup::class, PriceListItem::getTableName(), 'item_unit_id', 'pricing_group_id')
            ->withPivot(['price', 'discount_value', 'discount_percent', 'date', 'pricing_group_id']);
    }
}
