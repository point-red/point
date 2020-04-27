<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiScore extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'kpi_score';

    /**
     * Get the indicator that owns the template score.
     */
    public function indicator()
    {
        return $this->belongsTo(KpiIndicator::class);
    }
}
