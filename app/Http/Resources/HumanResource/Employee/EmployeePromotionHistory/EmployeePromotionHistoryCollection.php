<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeePromotionHistory;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Model\HumanResource\Employee\EmployeePromotionHistory;

class EmployeePromotionHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeePromotionHistory $employeePromotionHistory) {
            return new EmployeePromotionHistoryResource($employeePromotionHistory);
        });

        return parent::toArray($request);
    }
}
