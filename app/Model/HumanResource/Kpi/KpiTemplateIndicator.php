<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiTemplateIndicator extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the template group that owns the template indicator.
     */
    public function group()
    {
        return $this->belongsTo(get_class(new KpiTemplateGroup()));
    }
}
