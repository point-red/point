<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\ChartOfAccountGroup;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\Journal;
use App\Model\Master\User;

trait ChartOfAccountJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('account_type', $joins)) {
            $query = $query->join(ChartOfAccountType::getTableName().' as '.ChartOfAccountType::$alias,
                'account_type.id', '=', 'account.type_id');
        }

        if (in_array('account_group', $joins)) {
            $query = $query->join(ChartOfAccountGroup::getTableName().' as '.ChartOfAccountGroup::$alias,
                'account_group.id', '=', 'account.group_id');
        }

        if (in_array('journal', $joins)) {
            $query = $query->join(Journal::getTableName().' as '.Journal::$alias,
                Journal::$alias.'.chart_of_account_id', '=', 'account.id');
        }

        if (in_array('created_by', $joins)) {
            $query = $query->join(User::getTableName().' as created_by', 'created_by.id', '=',
                'account.created_by');
        }

        if (in_array('updated_by', $joins)) {
            $query = $query->join(User::getTableName().' as updated_by', 'updated_by.id', '=',
                'account.updated_by');
        }

        if (in_array('archived_by', $joins)) {
            $query = $query->join(User::getTableName().' as archived_by', 'archived_by.id', '=',
                'account.archived_by');
        }

        return $query;
    }
}
