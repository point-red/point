<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning phoneable models.
     */
    public function phoneable()
    {
        return $this->morphTo();
    }
}
