<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Model\PointModel;
use App\Traits\Model\Accounting\JournalJoin;

class Journal extends PointModel
{
    use JournalJoin;

    protected $connection = 'tenant';

    public static $morphName = 'Journal';

    public static $alias = 'journal';

    protected $table = 'journals';

    protected $casts = [
        'credit' => 'double',
        'debit' => 'double',
    ];

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
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
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
