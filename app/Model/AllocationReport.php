<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationReport extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'allocation_report';

    /**
     * Get the form that owns the AllocationReport.
     *
     * @return BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
