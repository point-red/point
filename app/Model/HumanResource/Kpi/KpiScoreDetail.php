<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiScoreDetail extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the score that owns the detail.
     */
    public function score()
    {
        return $this->belongsTo(get_class(new KpiScore()));
    }
}
