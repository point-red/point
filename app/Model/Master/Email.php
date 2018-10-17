<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning emailable models.
     */
    public function emailable()
    {
        return $this->morphTo();
    }
}
