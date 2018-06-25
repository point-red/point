<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeTrainingHistory;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeTrainingHistoryResource extends JsonResource
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
