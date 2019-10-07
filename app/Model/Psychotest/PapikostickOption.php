<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PapikostickOption extends Model
{
    
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_papikostick_options';

    public function question()
    {
        return $this->belongsTo('App\Model\Psychotest\PapikostickQuestion');
    }

    public function category()
    {
        return $this->belongsTo('App\Model\Psychotest\PapikostickCategory');
    }
    
}
