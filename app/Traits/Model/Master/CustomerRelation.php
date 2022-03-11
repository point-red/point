<?php

namespace App\Traits\Model\Master;

use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\Branch;
use App\Model\Master\ContactPerson;
use App\Model\Master\CustomerGroup;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use App\Model\Master\PricingGroup;

trait CustomerRelation
{
    /**
     * Get all of the groups for the customer.
     */
    public function groups()
    {
        return $this->belongsToMany(CustomerGroup::class);
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
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    /**
     * Get the customer's pricing group.
     */
    public function pricingGroup()
    {
        return $this->belongsTo(PricingGroup::class);
    }

    /**
     * Get the customer's branch.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the customer's payment.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    /**
     * Get the customer's payment.
     */
    public function cutoffPayments()
    {
        return $this->morphMany(CutOffPayment::class, 'cutoff_paymentable');
    }

    public function cutoffDownPayments()
    {
        return $this->morphMany(CutOffPayment::class, 'cutoff_downpaymentable');
    }
}
