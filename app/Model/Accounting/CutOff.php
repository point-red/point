<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class CutOff extends Model
{
    protected $connection = 'tenant';

    protected $table = 'cut_offs';
}
