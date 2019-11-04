<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class CandidatePosition extends Model
{
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_candidate_positions';

    public function position_category() {
        return $this->hasOne('App\Model\Psychotest\PositionCategory', 'position_id');
    }
}
