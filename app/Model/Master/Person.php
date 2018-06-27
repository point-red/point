<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $connection = 'tenant';

    protected $table = 'persons';

    public function category()
    {
        return $this->belongsTo('App\Model\Master\PersonCategory', 'person_category_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Model\Master\PersonGroup', 'person_group_id');
    }

    public function phones()
    {
        return $this->hasMany(get_class(new PersonPhone()));
    }

    public function addresses()
    {
        return $this->hasMany(get_class(new PersonAddress()));
    }

    public function emails()
    {
        return $this->hasMany(get_class(new PersonEmail()));
    }
}
