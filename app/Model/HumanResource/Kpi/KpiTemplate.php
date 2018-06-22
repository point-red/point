<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the person for the kpi template.
     */
    public function persons()
    {
        return $this->hasManyThrough('App\Model\Master\Person', 'App\Model\HumanResource\Kpi\KpiTemplatePerson', 'kpi_template_id', 'id', 'id', 'person_id');
    }

    public function groups()
    {
        return $this->hasMany('App\Model\HumanResource\Kpi\KpiTemplateGroup');
    }

    public function score()
    {
        return $this->hasOne('App\Model\HumanResource\Kpi\KpiScore');
    }
}
