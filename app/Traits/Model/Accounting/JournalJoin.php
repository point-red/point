<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\Journal;
use App\Model\Form;

trait JournalJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName() . ' as ' . Form::$alias, Form::$alias . '.id', '=', Journal::$alias . '.form_id');
        }

        return $query;
    }
}
