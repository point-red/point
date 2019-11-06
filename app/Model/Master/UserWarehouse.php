<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class UserWarehouse extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the user that has access to warehouse.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the warehouse that user is allowed to access.
     */
    public function warehouse()
    {
        return $this->belongsTo(warehouse::class, 'warehouse_id');
    }
}
