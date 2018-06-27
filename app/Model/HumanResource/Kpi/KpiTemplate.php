<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the kpi template.
     */
    public function employees()
    {
        return $this->hasMany('App\Model\HumanResource\Employee\Employee');
    }

    public function groups()
    {
        return $this->hasMany('App\Model\HumanResource\Kpi\KpiTemplateGroup');
    }

    public function indicators()
    {
        return $this->hasManyThrough('App\Model\HumanResource\Kpi\KpiTemplateIndicator', 'App\Model\HumanResource\Kpi\KpiTemplateGroup');
    }

    public function score()
    {
        return $this->hasOne('App\Model\HumanResource\Kpi\KpiScore');
    }
}
