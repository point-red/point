<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MasterHistory extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    /**
     * Get all of the owning historyable models.
     */
    public function historyable()
    {
        return $this->morphTo();
    }
}
