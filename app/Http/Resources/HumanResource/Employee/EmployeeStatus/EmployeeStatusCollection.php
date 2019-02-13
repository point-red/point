<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeStatus;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Model\HumanResource\Employee\EmployeeStatus;

class EmployeeStatusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeStatus $employeeStatus) {
            return new EmployeeStatusResource($employeeStatus);
        });

        return parent::toArray($request);
    }
}
