<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\HumanResource\Employee\Employee;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the kpi groups for the kpi category.
     */
    public function groups()
    {
        return $this->hasMany(get_class(new KpiGroup()));
    }

    public function indicators()
    {
        return $this->hasManyThrough('App\Model\HumanResource\Kpi\KpiIndicator', 'App\Model\HumanResource\Kpi\KpiGroup');
    }

    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()));
    }
}
