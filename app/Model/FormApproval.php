<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FormApproval extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the form that owns the form approval.
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
