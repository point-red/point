<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeContract;

use App\Model\HumanResource\Employee\EmployeeContract;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeContractCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeContract $employeeContract) {
            return new EmployeeContractResource($employeeContract);
        });

        return parent::toArray($request);
    }
}
