<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['name', 'tax_identification_number'];
}
