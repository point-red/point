<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\ChartOfAccount;
use App\Model\UserActivity;
use App\Model\Accounting\MemoJournal;
use App\Model\Accounting\MemoJournalItem;
use App\Model\Form;

trait MemoJournalJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', MemoJournal::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', MemoJournal::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(MemoJournalItem::getTableName().' as '.MemoJournalItem::$alias,
                MemoJournalItem::$alias.'.memo_journal_id', '=', MemoJournalItem::$alias.'.id');
            if (in_array('chart_of_account', $joins)) {
                $query = $query->leftjoin(ChartOfAccount::getTableName().' as '.ChartOfAccount::$alias,
                    ChartOfAccount::$alias.'.id', '=', MemoJournalItem::$alias.'.chart_of_account_id');
            }
        }

        return $query;
    }
}
