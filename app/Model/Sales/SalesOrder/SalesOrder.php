<?php

namespace App\Model\Sales\SalesOrder;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;
}
