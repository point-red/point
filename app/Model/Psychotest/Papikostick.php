<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class Papikostick extends Model
{
    
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_papikosticks';

    public function papikostick_results()
    {
        return $this->hasMany('App\Model\Psychotest\PapikostickResult', 'papikostick_id');
    }

    public function candidate()
    {
        return $this->belongsTo('App\Model\Psychotest\Candidate', 'candidate_id');
    }
    
}
