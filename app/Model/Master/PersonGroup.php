<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class PersonGroup extends Model
{
    public function persons() {
        return $this->hasMany('App\Model\Master\Person');
    }
}
