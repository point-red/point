<?php

namespace App\Helpers\Journal;

use App\Model\Accounting\Journal;
use App\Model\Form;
use Illuminate\Support\Facades\DB;

class BalanceHelper
{
    public static function openingBalance($date, $options = [])
    {
        $journals = Journal::join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
            ->select('chart_of_account_id')
            ->addSelect(DB::raw('max(forms.date) as date'))
            ->addSelect(DB::raw('sum(debit) as debit'))
            ->addSelect(DB::raw('sum(credit) as credit'))
            ->with('chartOfAccount')
            ->where('forms.date', '<', date('Y-m-d 00:00:00', strtotime($date)))
            ->groupBy('chart_of_account_id');

        // Exclude account that doesn't have any value
        if (in_array('without_zero', $options)) {
            $journals = $journals->hasValue();
        }

        return $journals->get();
    }

    public static function rangeBalance($fromDate, $untilDate, $options = [])
    {
        $journals = Journal::join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
            ->select('chart_of_account_id')
            ->addSelect(DB::raw('max(forms.date) as date'))
            ->addSelect(DB::raw('sum(debit) as debit'))
            ->addSelect(DB::raw('sum(credit) as credit'))
            ->with('chartOfAccount')
            ->where('forms.date', '>=', date('Y-m-d 00:00:00', strtotime($fromDate)))
            ->where('forms.date', '<', date('Y-m-d 00:00:00', strtotime($untilDate)))
            ->groupBy('chart_of_account_id');

        // Exclude account that doesn't have any value
        if (in_array('without_zero', $options)) {
            $journals = $journals->hasValue();
        }

        return $journals->get();
    }

    public static function endingBalance($date, $options = [])
    {
        $journals = Journal::join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
            ->select('chart_of_account_id')
            ->addSelect(DB::raw('max(forms.date) as date'))
            ->addSelect(DB::raw('sum(debit) as debit'))
            ->addSelect(DB::raw('sum(credit) as credit'))
            ->with('chartOfAccount')
            ->where('forms.date', '<=', date('Y-m-d 23:59:59', strtotime($date)))
            ->groupBy('chart_of_account_id');

        // Exclude account that doesn't have any value
        if (in_array('without_zero', $options)) {
            $journals = $journals->hasValue();
        }

        return $journals->get();
    }
}
