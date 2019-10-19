<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PapikostickResult extends Model
{
    
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_papikostick_results';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'total' => 0
    ];

    public function papikostick()
    {
        return $this->belongsTo('App\Model\Psychotest\Papikostick');
    }

    public function category()
    {
        return $this->belongsTo('App\Model\Psychotest\PapikostickCategory');
    }
    
}
