<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;

class Supplier extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'tax_identification_number',
        'notes',
        'disabled',
    ];

    /**
     * Get all of the groups for the supplier.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /**
     * Get all of the supplier's contact persons.
     */
    public function contactPersons()
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    /**
     * Get all of the supplier's address.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get all of the supplier's phones.
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get all of the supplier's emails.
     */
    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    /**
     * Get all of the supplier's banks.
     */
    public function banks()
    {
        return $this->morphMany(Bank::class, 'bankable');
    }

    /**
     * Get all of the supplier's journals.
     */
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    /**
     * Get the supplier's payment.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
