<?php

namespace App\Model\HumanResource\Psychology;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychology_candidates';
}
