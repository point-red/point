<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $connection = 'tenant';

    protected $table = 'chart_of_accounts';
}
