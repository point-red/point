<?php

namespace App\Traits\Model\Accounting;

use App\Model\Accounting\ChartOfAccountGroup;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\Journal;
use App\Model\Form;

trait ChatOfAccountRelation
{
    /**
     * Get the type that owns the chart of account.
     */
    public function type()
    {
        return $this->belongsTo(ChartOfAccountType::class, 'type_id');
    }

    /**
     * Get the group that owns the chart of account.
     */
    public function group()
    {
        return $this->belongsTo(ChartOfAccountGroup::class, 'group_id');
    }

    public function journals($date)
    {
        return $this->hasMany(Journal::class, 'chart_of_account_id')
            ->join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
            ->where('forms.date', '<=', $date);
    }
}
