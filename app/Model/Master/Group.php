<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Group extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['name', 'code', 'type'];

    private $masterNamespace = 'App\Model\Master\\';

    /**
     * Get all of the suppliers that are assigned this group.
     */
    public function suppliers()
    {
        return $this->morphedByMany(get_class(new Supplier()), 'groupable');
    }

    /**
     * Get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->morphedByMany(get_class(new Customer()), 'groupable');
    }

    /**
     * Get all of the items that are assigned this group.
     */
    public function items()
    {
        return $this->morphedByMany(get_class(new Item()), 'groupable');
    }

    /**
     * Get all of the services that are assigned this group.
     */
    public function services()
    {
        return $this->morphedByMany(get_class(new Service()), 'groupable');
    }

    /**
     * Get all of the allocations that are assigned this group.
     */
    public function allocations()
    {
        return $this->morphedByMany(get_class(new Allocation()), 'groupable');
    }

    /**
     * Set the group's type.
     *
     * @param  string  $value
     * @return void
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $this->masterNamespace . capitalize($value);
    }

    public $typeClass = [
        'supplier' => Supplier::class,
        'customer' => Customer::class,
        'item' => Item::class,
    ];

    public $groupTypeIsNotAvailableResponse = [
        'code' => 400,
        'message' => 'Group type is not available'
    ];

    public function isGroupTypeAvailable($groupType)
    {
        if (!array_key_exists($groupType, $this->typeClass)) {
            return false;
        }

        return true;
    }

    public function getTypeClass($type)
    {
        foreach ($this->typeClass as $key => $value) {
            if ($key == $type) {
                return $value;
            }
        }
    }
}
