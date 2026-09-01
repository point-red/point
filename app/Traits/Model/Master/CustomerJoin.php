<?php

namespace App\Traits\Model\Master;

use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\Branch;
use App\Model\Master\ContactPerson;
use App\Model\Master\Customer;
use App\Model\Master\Email;
use App\Model\Master\Phone;

trait CustomerJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('addresses', $joins)) {
            $query = $query->leftjoin(Address::getTableName(), function ($q) {
                $q->on(Address::getTableName('addressable_id'), '=', Customer::getTableName('id'))
                    ->where(Address::getTableName('addressable_type'), Customer::$morphName);
            });
        }

        if (in_array('phones', $joins)) {
            $query = $query->leftjoin(Phone::getTableName(), function ($q) {
                $q->on(Phone::getTableName('phoneable_id'), '=', Customer::getTableName('id'))
                    ->where(Phone::getTableName('phoneable_type'), Customer::$morphName);
            });
        }

        if (in_array('emails', $joins)) {
            $query = $query->leftjoin(Email::getTableName(), function ($q) {
                $q->on(Email::getTableName('emailable_id'), '=', Customer::getTableName('id'))
                    ->where(Email::getTableName('emailable_type'), Customer::$morphName);
            });
        }

        if (in_array('contact_persons', $joins)) {
            $query = $query->leftjoin(ContactPerson::getTableName(), function ($q) {
                $q->on(ContactPerson::getTableName('contactable_id'), '=', Customer::getTableName('id'))
                    ->where(ContactPerson::getTableName('contactable_type'), Customer::$morphName);
            });
        }

        if (in_array('banks', $joins)) {
            $query = $query->leftjoin(Bank::getTableName(), function ($q) {
                $q->on(Bank::getTableName('bankable_id'), '=', Customer::getTableName('id'))
                    ->where(Bank::getTableName('bankable_type'), Customer::$morphName);
            });
        }

        if (in_array('journals', $joins)) {
            $query = $query->leftjoin(Journal::getTableName(), function ($q) {
                $q->on(Journal::getTableName('journalable_id'), '=', Customer::getTableName('id'))
                    ->where(Journal::getTableName('journalable_type'), Customer::$morphName);
            });
        }

        if (in_array('payments', $joins)) {
            $query = $query->leftjoin(Payment::getTableName(), function ($q) {
                $q->on(Payment::getTableName('paymentable_id'), '=', Customer::getTableName('id'))
                    ->where(Payment::getTableName('paymentable_type'), Customer::$morphName);
            });
        }

        if (in_array('branch', $joins)) {
            $query = $query->leftjoin(Branch::getTableName().' as '.Branch::$alias, function ($q) {
                $q->on(Branch::$alias.'.id', '=', Customer::$alias.'.branch_id');
            });
        }

        if (in_array('pricing_groups', $joins)) {
            $query = $query->leftjoin('pricing_groups', function ($q) {
                $q->on('pricing_groups.id', '=', Customer::$alias.'.pricing_group_id');
            });
        }

        if (in_array('groups', $joins)) {
            $query = $query->leftjoin('customer_customer_group', function ($q) {
                $q->on('customer_customer_group.customer_id', '=', Customer::$alias.'.id');
            });
            $query = $query->leftjoin('customer_groups', function ($q) {
                $q->on('customer_groups.id', '=', 'customer_customer_group.customer_group_id');
            });
        }

        return $query;
    }
}
