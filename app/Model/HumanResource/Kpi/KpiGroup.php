<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiGroup extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the kpis for the group.
     */
    public function indicators()
    {
        return $this->hasMany(KpiIndicator::class);
    }

    /**
     * Get the kpi category that owns kpi group.
     */
    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }
}
