<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class PersonCategory extends Model
{
    public function persons() {
        return $this->hasMany('App\Model\Master\Person');
    }
}
