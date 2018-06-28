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
        return $this->belongsTo(get_class(new KpiGroup()));
    }
}
