<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $connection = 'tenant';

    protected $table = 'journals';
}
