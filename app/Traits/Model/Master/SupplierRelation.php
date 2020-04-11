<?php

namespace App\Traits\Model\Master;

use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\ContactPerson;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use App\Model\Master\SupplierGroup;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

trait SupplierRelation
{
    /**
     * Get all of the groups for the supplier.
     */
    public function groups()
    {
        return $this->belongsToMany(SupplierGroup::class);
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

    /**
     * Get the supplier's purchase receives.
     */
    public function purchaseReceives()
    {
        return $this->hasMany(PurchaseReceive::class);
    }
}
