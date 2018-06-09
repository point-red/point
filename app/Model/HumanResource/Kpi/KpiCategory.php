<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\Master\Person;
use Illuminate\Database\Eloquent\Model;

class KpiCategory extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the kpi groups for the kpi category.
     */
    public function groups()
    {
        return $this->hasMany(get_class(new KpiGroup()));
    }

    /**
     * Get persons for the kpi category.
     */
    public function persons()
    {
        return $this->hasMany(get_class(new Person()));
    }
}
