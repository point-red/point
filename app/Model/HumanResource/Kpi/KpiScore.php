<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiScore extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the details for the kpi score.
     */
    public function details()
    {
        return $this->hasMany(get_class(new KpiScoreDetail()));
    }
}
