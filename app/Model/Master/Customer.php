<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'tax_identification_number',
        'group_id',
        'pricing_group_id',
        'code',
        'tax_identification_number',
        'notes',
        'credit_ceiling'
    ];

    /**
     * Get the group that owns the customer.
     */
    public function group()
    {
        return $this->belongsTo(get_class(new CustomerGroup()), 'customer_group_id');
    }
}
