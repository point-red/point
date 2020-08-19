<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAdditionalComponent;

use App\Model\HumanResource\Employee\EmployeeSalaryAdditionalComponent;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeSalaryAdditionalComponentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalaryAdditionalComponent $employeeSalaryAdditionalComponent) {
            return new EmployeeSalaryAdditionalComponentResource($employeeSalaryAdditionalComponent);
        });

        return parent::toArray($request);
    }
}
