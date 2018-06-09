<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiTemplateGroup extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the kpi template that owns the group.
     */
    public function template()
    {
        return $this->belongsTo(get_class(new KpiTemplate()));
    }

    /**
     * Get the indicators for the group.
     */
    public function indicators()
    {
        return $this->hasMany(get_class(new KpiTemplateIndicator()));
    }
}
