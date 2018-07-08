<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeGroup;

use App\Model\HumanResource\Employee\EmployeeGroup;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeGroup $employeeGroup) {
            return new EmployeeGroupResource($employeeGroup);
        });

        return parent::toArray($request);
    }
}
