<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\User;
use App\Model\TransactionModel;

class Kpi extends TransactionModel
{
    protected $connection = 'tenant';

    /**
     * Get the kpi groups for the kpi.
     */
    public function groups()
    {
        return $this->hasMany(KpiGroup::class);
    }

    /**
     * Get the kpi indicators for the kpi.
     */
    public function indicators()
    {
        return $this->hasManyThrough(KpiIndicator::class, KpiGroup::class);
    }

    /**
     * Get the employee that owns the kpi.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the scorer that owns the kpi.
     */
    public function scorer()
    {
        return $this->belongsTo(User::class);
    }
}
