<?php

namespace App\Model\HumanResource\Kpi;

use App\Model\Master\User;
use App\Model\TransactionModel;
use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\Employee;

class Kpi extends TransactionModel
{
    protected $connection = 'tenant';

    /**
     * Get the kpi groups for the kpi.
     */
    public function groups()
    {
        return $this->hasMany(get_class(new KpiGroup()));
    }

    /**
     * Get the kpi indicators for the kpi.
     */
    public function indicators()
    {
        return $this->hasManyThrough('App\Model\HumanResource\Kpi\KpiIndicator', 'App\Model\HumanResource\Kpi\KpiGroup');
    }

    /**
     * Get the employee that owns the kpi.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()));
    }

    /**
     * Get the scorer that owns the kpi.
     */
    public function scorer()
    {
        return $this->belongsTo(get_class(new User()));
    }
}
