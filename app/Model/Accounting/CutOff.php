<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Model\TransactionModel;

class CutOff extends TransactionModel
{
    public static $morphName = 'CutOff';

    protected $connection = 'tenant';

    public static $alias = 'cut_off';

    protected $table = 'cut_offs';

    public $timestamps = false;

    public $defaultNumberPrefix = 'CUT';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }
}
