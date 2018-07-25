<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class CutOffDetail extends Model
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_details';
}
