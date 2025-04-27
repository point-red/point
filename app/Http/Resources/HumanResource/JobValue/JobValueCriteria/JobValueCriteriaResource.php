<?php

namespace App\Http\Resources\HumanResource\JobValue\JobValueCriteria;

use Illuminate\Http\Resources\Json\JsonResource;

class JobValueCriteriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'base_salary' => $this->base_salary,
            'multiplier_kpi' => $this->multiplier_kpi,
        ];
    }
}
