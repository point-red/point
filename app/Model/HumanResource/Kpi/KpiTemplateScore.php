<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\MasterModel;

class KpiTemplateScore extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the template indicator that owns the template score.
     */
    public function indicator()
    {
        return $this->belongsTo(KpiTemplateIndicator::class);
    }
}
