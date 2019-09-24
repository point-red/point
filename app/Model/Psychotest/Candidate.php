<?php

namespace App\Model\Psychotest;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
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
        'is_password_used' => false
    ];

    public function kraepelin() {
        return $this->hasOne('App\Model\Psychotest\Kraepelin');
    }
}
