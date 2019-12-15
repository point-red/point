<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AllocationReport extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the form that owns the AllocationReport.
     *
     *@return eloquent
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
