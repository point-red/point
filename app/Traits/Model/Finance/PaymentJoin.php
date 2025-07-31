<?php

namespace App\Traits\Model\Finance;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Allocation;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;

trait PaymentJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->joinForm();
        }

        if (in_array('payment_account', $joins)) {
            $query = $query->leftjoin(ChartOfAccount::getTableName().' as payment_account',
                'payment_account.id', '=', Payment::$alias.'.payment_account_id');
        }

        if (in_array('details', $joins)) {
            $query = $query->leftjoin(PaymentDetail::getTableName().' as '.PaymentDetail::$alias,
                PaymentDetail::$alias.'.payment_id', '=', Payment::$alias.'.id');
            if (in_array('account', $joins)) {
                $query = $query->leftjoin(ChartOfAccount::getTableName().' as '.ChartOfAccount::$alias,
                    ChartOfAccount::$alias.'.id', '=', PaymentDetail::$alias.'.chart_of_account_id');
            }
            if (in_array('allocation', $joins)) {
                $query = $query->leftjoin(Allocation::getTableName().' as '.Allocation::$alias,
                    Allocation::$alias.'.id', '=', PaymentDetail::$alias.'.allocation_id');
            }
        }

        if (in_array('paymentable', $joins)) {
            $query = $query->leftJoin(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(Customer::$alias.'.id', '=', Payment::$alias.'.paymentable_id')
                    ->where(Payment::$alias.'.paymentable_type', Customer::$morphName);
            });

            $query = $query->leftJoin(Supplier::getTableName().' as '.Supplier::$alias, function ($q) {
                $q->on(Supplier::$alias.'.id', '=', Payment::$alias.'.paymentable_id')
                    ->where(Payment::$alias.'.paymentable_type', Supplier::$morphName);
            });

            $query = $query->leftJoin(Employee::getTableName().' as '.Employee::$alias, function ($q) {
                $q->on(Employee::$alias.'.id', '=', Payment::$alias.'.paymentable_id')
                    ->where(Payment::$alias.'.paymentable_type', Employee::$morphName);
            });
        }

        if (in_array('cashAdvances', $joins)) {
            $query = $query->with(['cashAdvances']);
        }

        return $query;
    }
}
