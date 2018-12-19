<?php

namespace App\Model\Sales\SalesQuotation;

use Illuminate\Database\Eloquent\Model;

class SalesQuotationService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;
}
