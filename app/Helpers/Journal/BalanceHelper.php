<?php

namespace App\Helpers\Journal;

use App\Model\Accounting\Journal;

class BalanceHelper
{
    public static function endingBalance($date, $options = [])
    {
        $journals = Journal::select('journals.*')->whereIn('date', function ($q) use ($date) {
            $q->selectRaw('max(date)')
                ->where('date', '<=', date('Y-m-d 23:59:59', strtotime($date)))
                ->from('journals')
                ->groupBy('chart_of_account_id');
        });

        if (in_array('without_zero', $options)) {
            $journals = $journals->where(function ($q) {
                $q->where('debit', '!=', 0)->orWhere('credit', '!=', 0);
            });
        }

        return $journals->get();
    }
}
