<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiIndicator extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the group that owns the kpi.
     */
    public function group()
    {
        return $this->belongsTo(KpiGroup::class);
    }

    /**
     * Get the scores for the indicator.
     */
    public function scores()
    {
        return $this->hasMany(KpiScore::class);
    }
}
