<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Model\FormApproval;
use App\Model\TransactionModel;

class CutOff extends TransactionModel
{
    public static $morphName = 'CutOff';

    protected $connection = 'tenant';

    protected $table = 'cut_offs';

    public $timestamps = false;

    public $defaultNumberPrefix = 'CUT';

    /**
     * Get the details for the cut off.
     */
    public function details()
    {
        return $this->hasMany(CutOffDetail::class, 'cut_off_id');
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function approvers()
    {
        return $this->hasManyThrough(FormApproval::class, Form::class, 'formable_id', 'form_id')->where('formable_type', self::$morphName);
    }
}
