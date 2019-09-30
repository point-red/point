<?php

namespace App\Model\Psychotest;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class Kraepelin extends Model
{

    use EloquentFilters;

    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychotest_kraepelins';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'column_duration' => 15000,
        'total_correct' => 0,
        'total_count' => 0
    ];

    public function candidate()
    {
        return $this->belongsTo('App\Model\Psychotest\Candidate');
    }

    public function active_column() {
        return $this->belongsTo('App\Model\Psychotest\KraepelinColumn');
    }

    public function kraepelin_columns() {
        return $this->hasMany('App\Model\Psychotest\KraepelinColumn');
    }
}
