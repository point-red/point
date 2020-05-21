<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeSalaryAdditionalComponent extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_salary_additional_components';

    public static function isAboveMaximumWeight($weight, $previousId = null)
    {
    	$total_weight = 0;

    	foreach (static::all() as $additionalComponent) {
    		if ($previousId === null || $previousId !== $additionalComponent->id) {
	    		$total_weight += $additionalComponent->weight;
	    	}
    	}

    	$total_weight += $weight;

    	if ($total_weight > 100) {
    		return true;
    	}

    	return false;
    }
}
