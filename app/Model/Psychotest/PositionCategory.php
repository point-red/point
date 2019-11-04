<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PositionCategory extends Model
{
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_position_categories';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'category_max' => 15,
        'category_min' => 0
    ];

    public function position() {
        return $this->belongsTo('App\Model\Psychotest\CandidatePosition');
    }

    public function category() {
        return $this->belongsTo('App\Model\Psychotest\PapikostickCategory');
    }
}
