<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'persons';

    public function category() {
        return $this->belongsTo('App\Model\Master\PersonCategory');
    }

    public function group() {
        return $this->belongsTo('App\Model\Master\PersonGroup');
    }
}
