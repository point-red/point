<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeReligion;

use App\Model\HumanResource\Employee\EmployeeReligion;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeReligionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeReligion $employeeReligion) {
            return new EmployeeReligionResource($employeeReligion);
        });

        return parent::toArray($request);
    }
}
