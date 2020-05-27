<?php

namespace App\Traits\Model\Finance;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Item;
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
