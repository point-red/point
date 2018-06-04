<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class PersonCategory extends Model
{
    protected $connection = 'tenant';

    public function persons()
    {
        return $this->hasMany('App\Model\Master\Person');
    }
}
