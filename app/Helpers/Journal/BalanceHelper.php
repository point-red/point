<?php

namespace App\Helpers\Journal;

use App\Model\Accounting\Journal;
use Illuminate\Support\Facades\DB;

class BalanceHelper
{
    public static function openingBalance($date, $options = [])
    {
        $journals = Journal::select('chart_of_account_id')
            ->addSelect(DB::raw('max(date) as date'))
            ->addSelect(DB::raw('sum(debit) as debit'))
            ->addSelect(DB::raw('sum(credit) as credit'))
            ->with('chartOfAccount')
            ->where('date', '<', date('Y-m-d 00:00:00', strtotime($date)))
            ->groupBy('chart_of_account_id');

        // Exclude account that doesn't have any value
        if (in_array('without_zero', $options)) {
            $journals = $journals->hasValue();
        }

        return $journals->get();
    }

    public static function endingBalance($date, $options = [])
    {
        $journals = Journal::select('chart_of_account_id')
            ->addSelect(DB::raw('max(date) as date'))
            ->addSelect(DB::raw('sum(debit) as debit'))
            ->addSelect(DB::raw('sum(credit) as credit'))
            ->with('chartOfAccount')
            ->where('date', '<=', date('Y-m-d 23:59:59', strtotime($date)))
            ->groupBy('chart_of_account_id');

        // Exclude account that doesn't have any value
        if (in_array('without_zero', $options)) {
            $journals = $journals->hasValue();
        }

        return $journals->get();
    }
}
