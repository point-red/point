<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['name', 'tax_identification_number'];

    /**
     * Get the group that owns the customer.
     */
    public function group()
    {
        return $this->belongsTo(get_class(new CustomerGroup()), 'customer_group_id');
    }
}
