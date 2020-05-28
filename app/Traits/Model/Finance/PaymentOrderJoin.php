<?php

namespace App\Traits\Model\Finance;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\Finance\PaymentOrder\PaymentOrderDetail;
use App\Model\Master\Supplier;

trait PaymentOrderJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName() . ' as ' . Form::$alias, function ($q) {
                $q->on(Form::$alias . '.formable_id', '=', PaymentOrder::$alias . '.id')
                    ->where(Form::$alias . '.formable_type', PaymentOrder::$morphName);
            });
        }

        if (in_array('paymentable', $joins)) {
            $query = $query->leftJoin(Customer::getTableName() . ' as ' . Customer::$alias, function ($q) {
                $q->on(Customer::$alias . '.id', '=', PaymentOrder::$alias . '.paymentable_id')
                    ->where(PaymentOrder::$alias . '.paymentable_type', Customer::$morphName);
            });

            $query = $query->leftJoin(Supplier::getTableName() . ' as ' . Supplier::$alias, function ($q) {
                $q->on(Supplier::$alias . '.id', '=', PaymentOrder::$alias . '.paymentable_id')
                    ->where(PaymentOrder::$alias . '.paymentable_type', Supplier::$morphName);

            });

            $query = $query->leftJoin(Employee::getTableName() . ' as ' . Employee::$alias, function ($q) {
                $q->on(Employee::$alias . '.id', '=', PaymentOrder::$alias . '.paymentable_id')
                    ->where(PaymentOrder::$alias . '.paymentable_type', Employee::$morphName);
            });
        }

        if (in_array('details', $joins)) {
            $query = $query->leftjoin(PaymentOrderDetail::getTableName() . ' as ' . PaymentOrderDetail::$alias,
                PaymentOrderDetail::$alias . '.payment_order_id', '=', PaymentOrder::$alias . '.id');
            if (in_array('account', $joins)) {
                $query = $query->leftjoin(ChartOfAccount::getTableName() . ' as ' . ChartOfAccount::$alias,
                    ChartOfAccount::$alias . '.id', '=', PaymentOrderDetail::$alias . '.chart_of_account_id');
            }
        }

        return $query;
    }
}
