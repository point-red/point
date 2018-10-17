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

    /**
     * Get all of the customer's contact persons.
     */
    public function contactPersons()
    {
        return $this->morphMany(get_class(new ContactPerson()), 'contactable');
    }

    /**
     * Get all of the customer's address.
     */
    public function addresses()
    {
        return $this->morphMany(get_class(new Address()), 'addressable');
    }

    /**
     * Get all of the customer's phones.
     */
    public function phones()
    {
        return $this->morphMany(get_class(new Phone()), 'phoneable');
    }

    /**
     * Get all of the customer's emails.
     */
    public function emails()
    {
        return $this->morphMany(get_class(new Email()), 'emailable');
    }

    /**
     * Get all of the customer's banks.
     */
    public function banks()
    {
        return $this->morphMany(get_class(new Bank()), 'bankable');
    }
}
