<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeJobLocation;

use App\Model\HumanResource\Employee\EmployeeJobLocation;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeJobLocationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeJobLocation $employeeJobLocation) {
            return new EmployeeJobLocationResource($employeeJobLocation);
        });

        return parent::toArray($request);
    }
}
