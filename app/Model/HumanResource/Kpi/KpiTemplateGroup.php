<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiTemplateGroup extends Model
{
    protected $connection = 'tenant';

    protected $appends = ['target', 'weight'];

    public function getTargetAttribute()
    {
        return $this->indicators->sum('target');
    }

    public function getWeightAttribute()
    {
        return $this->indicators->sum('weight');
    }

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
