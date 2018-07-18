<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeGender;

use App\Model\HumanResource\Employee\EmployeeGender;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeGenderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeGender $employeeGender) {
            return new EmployeeGenderResource($employeeGender);
        });

        return parent::toArray($request);
    }
}
