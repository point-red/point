<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\MasterModel;

class KpiTemplateIndicator extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'kpi_template_indicator';

    /**
     * Get the template group that owns the template indicator.
     */
    public function group()
    {
        return $this->belongsTo(KpiTemplateGroup::class, 'kpi_template_group_id');
    }

    /**
     * Get the scores template for the indicator.
     */
    public function scores()
    {
        return $this->hasMany(KpiTemplateScore::class);
    }
}
