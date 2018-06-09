<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiGroup extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the kpis for the group.
     */
    public function kpis()
    {
        return $this->hasMany(get_class(new Kpi()));
    }

    /**
     * Get the kpi category that owns kpi group.
     */
    public function category()
    {
        return $this->belongsTo(get_class(new KpiCategory()));
    }
}
