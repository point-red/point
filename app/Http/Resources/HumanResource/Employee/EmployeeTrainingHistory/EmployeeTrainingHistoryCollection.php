<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeTrainingHistory;

use App\Model\HumanResource\Employee\EmployeeTrainingHistory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeTrainingHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeTrainingHistory $employeeTrainingHistory) {
            return new EmployeeTrainingHistoryResource($employeeTrainingHistory);
        });

        return parent::toArray($request);
    }
}
