<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\MasterModel;

class KpiTemplateGroup extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'kpi_template_group';

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
        return $this->belongsTo(KpiTemplate::class);
    }

    /**
     * Get the indicators for the group.
     */
    public function indicators()
    {
        return $this->hasMany(KpiTemplateIndicator::class);
    }
}
