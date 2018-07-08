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

    /**
     * Get the template groups for the kpi template.
     */
    public function groups()
    {
        return $this->hasMany('App\Model\HumanResource\Kpi\KpiTemplateGroup');
    }

    /**
     * Get the template indicators for the kpi template.
     */
    public function indicators()
    {
        return $this->hasManyThrough('App\Model\HumanResource\Kpi\KpiTemplateIndicator', 'App\Model\HumanResource\Kpi\KpiTemplateGroup');
    }
}
