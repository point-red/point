<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeePromotionHistory;

use Illuminate\Http\Resources\Json\ResourceCollection;

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
        return parent::toArray($request);
    }
}
