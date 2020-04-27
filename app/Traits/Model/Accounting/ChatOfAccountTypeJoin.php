<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\User;

trait ChartOfAccountTypeJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        if (in_array('account', $joins)) {
            $query = $query->join(ChartOfAccount::getTableName() . ' as ' . ChartOfAccount::$alias,
                'account.type_id', '=', 'account_type.id');
        }

        if (in_array('created_by', $joins)) {
            $query = $query->join(User::getTableName() . ' as created_by', 'created_by.id', '=',
                'account.created_by');
        }

        if (in_array('updated_by', $joins)) {
            $query = $query->join(User::getTableName() . ' as updated_by', 'updated_by.id', '=',
                'account.updated_by');
        }

        if (in_array('archived_by', $joins)) {
            $query = $query->join(User::getTableName() . ' as archived_by', 'archived_by.id', '=',
                'account.archived_by');
        }

        return $query;
    }
}
