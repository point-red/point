<?php

namespace App\Traits\Model\Master;

use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\ContactPerson;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use App\Model\Master\Supplier;

trait SupplierJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('addresses', $joins)) {
            $query = $query->leftjoin(Address::getTableName(), function ($q) {
                $q->on(Address::getTableName('addressable_id'), '=', Supplier::getTableName('id'))
                    ->where(Address::getTableName('addressable_type'), Supplier::$morphName);
            });
        }

        if (in_array('phones', $joins)) {
            $query = $query->leftjoin(Phone::getTableName(), function ($q) {
                $q->on(Phone::getTableName('phoneable_id'), '=', Supplier::getTableName('id'))
                    ->where(Phone::getTableName('phoneable_type'), Supplier::$morphName);
            });
        }

        if (in_array('emails', $joins)) {
            $query = $query->leftjoin(Email::getTableName(), function ($q) {
                $q->on(Email::getTableName('emailable_id'), '=', Supplier::getTableName('id'))
                    ->where(Email::getTableName('emailable_type'), Supplier::$morphName);
            });
        }

        if (in_array('contact_persons', $joins)) {
            $query = $query->leftjoin(ContactPerson::getTableName(), function ($q) {
                $q->on(ContactPerson::getTableName('contactable_id'), '=', Supplier::getTableName('id'))
                    ->where(ContactPerson::getTableName('contactable_type'), Supplier::$morphName);
            });
        }

        if (in_array('banks', $joins)) {
            $query = $query->leftjoin(Bank::getTableName(), function ($q) {
                $q->on(Bank::getTableName('bankable_id'), '=', Supplier::getTableName('id'))
                    ->where(Bank::getTableName('bankable_type'), Supplier::$morphName);
            });
        }

        if (in_array('journals', $joins)) {
            $query = $query->leftjoin(Journal::getTableName(), function ($q) {
                $q->on(Journal::getTableName('journalable_id'), '=', Supplier::getTableName('id'))
                    ->where(Journal::getTableName('journalable_type'), Supplier::$morphName);
            });
        }

        if (in_array('payments', $joins)) {
            $query = $query->leftjoin(Payment::getTableName(), function ($q) {
                $q->on(Payment::getTableName('paymentable_id'), '=', Supplier::getTableName('id'))
                    ->where(Payment::getTableName('paymentable_type'), Supplier::$morphName);
            });
        }

        return $query;
    }
}
