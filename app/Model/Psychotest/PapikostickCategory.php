<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PapikostickCategory extends Model
{
    
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'max' => 9,
        'min' => 0
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_papikostick_categories';

}
