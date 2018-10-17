<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning contactable models.
     */
    public function contactable()
    {
        return $this->morphTo();
    }
}
