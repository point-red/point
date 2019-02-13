<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeBaseSalary;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Model\HumanResource\Employee\EmployeeBaseSalary;

class EmployeeBaseSalaryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeBaseSalary $employeeBaseSalary) {
            return new EmployeeBaseSalaryResource($employeeBaseSalary);
        });

        return parent::toArray($request);
    }
}
