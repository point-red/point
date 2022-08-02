<?php

namespace App\Model\Accounting;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\MemoJournal;
use App\Model\Form;
use App\Model\TransactionModel;

class MemoJournalItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'memo_journal_item';

    public $timestamps = false;

    protected $casts = [
        'debit' => 'double',
        'credit' => 'double',
    ];

    protected $fillable = [
        'chart_of_account_id',
        'chart_of_account_name',
        'form_id',
        'masterable_id',
        'masterable_type',
        'debit',
        'credit',
        'notes',
    ];

    public function memo_journal()
    {
        return $this->belongsTo(MemoJournal::class);
    }

    public function chart_of_account()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function masterable() {
        return $this->morphTo();
    }
}
