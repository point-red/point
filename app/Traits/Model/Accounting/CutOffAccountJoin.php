<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use App\Model\Form;

trait CutOffAccountJoin
{
    public static function joins($query, $req)
    {
        $joins = explode(',', $req->get('join'));

        if (!$joins) {
            return $query;
        }

        if (in_array('cutoff.form', $joins)) {
            $query = $query->join(CutOff::getTableName().' as '.CutOff::$alias, function ($q) {
                $q->on(CutOff::$alias.'.id', '=', self::getTableName('cutoff_id'));
            })->join(Form::getTableName() . ' as ' . Form::$alias, function ($q) {
                $q->on(Form::$alias . '.formable_id', '=', CutOff::$alias.'.id')
                    ->where(Form::$alias . '.formable_type', CutOff::$morphName);
            });
        }

        if (in_array('account', $joins)) {
            $query = $query->join(ChartOfAccount::getTableName().' as '.ChartOfAccount::$alias,
                'account.id', '=', CutOffAccount::$alias.'.chart_of_account_id');
        }

        return $query;
    }
}
