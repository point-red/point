<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{

    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_candidates';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_password_used' => false,
        'is_kraepelin_started' => false,
        'is_kraepelin_finished' => false,

        'is_papikostick_started' => false,
        'current_papikostick_index' => 0,
        'is_papikostick_finished' => false
    ];

    public function kraepelin() {
        return $this->hasOne('App\Model\Psychotest\Kraepelin');
    }
}
