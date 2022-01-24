<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\General\FormableOne;
use App\Traits\Model\General\GeneralJoin;

class CutOff extends TransactionModel
{
    use GeneralJoin, FormableOne;

    protected $fillable = [
        'date',
        'chart_of_account_id',
    ];

    public static $morphName = 'CutOff';

    protected $connection = 'tenant';

    public static $alias = 'cut_off';

    protected $table = 'cutoffs_new';

    public $defaultNumberPrefix = 'CUT';
}
