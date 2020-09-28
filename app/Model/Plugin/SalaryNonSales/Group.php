<?php

namespace App\Model\Plugin\SalaryNonSales;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $connection = 'tenant';

    protected $table = 'jobvalue_groups';

    protected $fillable = ['name'];

    public function factors()
    {
        return $this->hasMany(GroupFactor::class, 'groupId');
    }
}
