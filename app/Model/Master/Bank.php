<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning bankable models.
     */
    public function bankable()
    {
        return $this->morphTo();
    }
}
