<?php

namespace App\Http\Resources\HumanResource\Employee\Employee;

use App\Model\HumanResource\Employee\Employee;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Employee $employee) {
            return (new EmployeeResource($employee));
        });

        return parent::toArray($request);
    }
}
