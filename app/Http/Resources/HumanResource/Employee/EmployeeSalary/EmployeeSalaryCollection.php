<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalary;

use App\Model\HumanResource\Employee\EmployeeSalary;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeSalaryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalary $employeeSalary) {
            return new EmployeeSalaryResource($employeeSalary);
        });

        return parent::toArray($request);
    }
}
