<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AllocationReport extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'allocation_report';

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
