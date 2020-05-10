<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PapikostickCategory extends Model
{
    
    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_papikostick_categories';

    public function position_category() {
        return $this->hasOne('App\Model\Psychotest\PositionCategory', 'category_id');
    }
}
