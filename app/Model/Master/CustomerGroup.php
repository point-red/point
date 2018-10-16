<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['code', 'name'];
}
