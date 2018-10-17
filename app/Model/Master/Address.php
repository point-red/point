<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning addressable models.
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
