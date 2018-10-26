<?php

namespace App\Model\Master;

use App\Model\Accounting\Journal;
use App\Model\MasterModel;

class Customer extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'tax_identification_number',
        'notes',
        'credit_ceiling',
        'pricing_group_id',
        'disabled',
    ];

    /**
     * Get all of the groups for the customer.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /**
     * Get all of the customer's contact persons.
     */
    public function contactPersons()
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    /**
     * Get all of the customer's address.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get all of the customer's phones.
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get all of the customer's emails.
     */
    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    /**
     * Get all of the customer's banks.
     */
    public function banks()
    {
        return $this->morphMany(Bank::class, 'bankable');
    }

    /**
     * Get all of the customer's journals.
     */
    public function journalable()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }
}
