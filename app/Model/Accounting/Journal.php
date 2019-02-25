<?php

namespace App\Model\Accounting;

use App\Model\Form;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $connection = 'tenant';

    protected $table = 'journals';

    /**
     * The form that belong to the journal.
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /**
     * The form reference that belong to the journal.
     */
    public function formReference()
    {
        return $this->belongsTo(Form::class, 'form_reference_id');
    }

    /**
     * The chart of account that belong to the journal.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(get_class(new ChartOfAccount()), 'chart_of_account_id');
    }

    public function scopeHasValue($query)
    {
        $query->where(function ($q) {
            $q->where('debit', '!=', 0)->orWhere('credit', '!=', 0);
        });
    }

    /**
     * Get all of the owning journalable models.
     */
    public function journalable()
    {
        return $this->morphTo();
    }
}
