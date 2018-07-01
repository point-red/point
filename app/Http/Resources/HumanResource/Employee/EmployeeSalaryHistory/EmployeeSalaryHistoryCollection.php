<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryHistory;

use App\Model\HumanResource\Employee\EmployeeSalaryHistory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeSalaryHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalaryHistory $employeeSalaryHistory) {
            return new EmployeeSalaryHistoryResource($employeeSalaryHistory);
        });

        return parent::toArray($request);
    }
}
