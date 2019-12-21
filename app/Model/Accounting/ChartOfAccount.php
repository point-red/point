<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Model\MasterModel;

class ChartOfAccount extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_accounts';

    protected $appends = ['label'];

    public function getLabelAttribute()
    {
        return $this->number . ' - ' . $this->alias;
    }

    /**
     * Get the type that owns the chart of account.
     */
    public function type()
    {
        return $this->belongsTo(ChartOfAccountType::class, 'type_id');
    }

    /**
     * Get the type that owns the chart of account.
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

    public function totalDebit($date)
    {
        return $this->journals($date)->sum('debit');
    }

    public function totalCredit($date)
    {
        return $this->journals($date)->sum('credit');
    }

    public function total($date)
    {
        if ($this->type->is_debit) {
            return $this->totalDebit($date) - $this->totalCredit($date);
        }

        return $this->totalCredit($date) - $this->totalDebit($date);
    }
}
