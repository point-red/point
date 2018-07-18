<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeMaritalStatus;

use App\Model\HumanResource\Employee\EmployeeMaritalStatus;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeMaritalStatusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeMaritalStatus $employeeMaritalStatus) {
            return new EmployeeMaritalStatusResource($employeeMaritalStatus);
        });

        return parent::toArray($request);
    }
}
