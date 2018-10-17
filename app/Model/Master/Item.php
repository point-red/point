<?php

namespace App\Model\Master;

use App\Model\Group;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'tenant';

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(get_class(new Group()), 'groupable');
    }
}
