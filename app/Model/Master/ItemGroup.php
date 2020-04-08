<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ItemGroup extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'item_group';

    protected $fillable = ['name', 'type'];

    /**
     * get all of the items that are assigned this group.
     */
    public function items()
    {
        return $this->belongstomany(Item::class);
    }
}
