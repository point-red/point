<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FormCancellation extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the form that owns the form cancellation.
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
