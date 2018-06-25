<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeePromotionHistory;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePromotionHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
