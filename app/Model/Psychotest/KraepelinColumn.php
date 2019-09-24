<?php

namespace App\Model\Psychotest;

use Illuminate\Database\Eloquent\Model;

class KraepelinColumn extends Model
{
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_kraepelin_columns';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'correct' => 0,
        'count' => 0
    ];

    public function kraepelin() {
        return $this->belongsTo('App\Model\Psychotest\Kraepelin');
    }
}
