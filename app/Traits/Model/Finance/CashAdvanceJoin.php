<?php

namespace App\Traits\Model\Finance;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Finance\CashAdvance\CashAdvanceDetail;
use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;

trait CashAdvanceJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', CashAdvance::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', CashAdvance::$morphName);
            });
        }

        if (in_array('employee', $joins)) {
            $query = $query->leftJoin(Employee::getTableName().' as '.Employee::$alias, function ($q) {
                $q->on(Employee::$alias.'.id', '=', CashAdvance::$alias.'.employee_id');
            });
        }

        if (in_array('details', $joins)) {
            $query = $query->leftjoin(CashAdvanceDetail::getTableName().' as '.CashAdvanceDetail::$alias,
                CashAdvanceDetail::$alias.'.cash_advance_id', '=', CashAdvance::$alias.'.id');
            if (in_array('account', $joins)) {
                $query = $query->leftjoin(ChartOfAccount::getTableName().' as '.ChartOfAccount::$alias,
                    ChartOfAccount::$alias.'.id', '=', CashAdvanceDetail::$alias.'.chart_of_account_id');
            }
        }

        return $query;
    }
}
