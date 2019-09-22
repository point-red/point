<?php

namespace App\Model\HumanResource\Psychology;

use Illuminate\Database\Eloquent\Model;

class Kraeplin extends Model
{
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psychology_kraeplins';

    public function candidate()
    {
        return $this->belongsTo('App\Model\HumanResource\Psychology\Candidate');
    }
}
