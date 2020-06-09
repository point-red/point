<?php

namespace App\Model;

use App\Model\Accounting\ChartOfAccount;
use Illuminate\Database\Eloquent\Model;

class SettingJournal extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'setting_journal';

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
