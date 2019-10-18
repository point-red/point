<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PapikostickQuestion extends Model
{
    
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_papikostick_questions';
    
    public function papikostick_options()
    {
        return $this->hasMany('App\Model\Psychotest\PapikostickOption', 'question_id');
    }
}
