<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiCategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupResource;
use App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource;

class KpiResource extends JsonResource
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
            'date' => $this->date,
            'employee' => new EmployeeResource($this->employee),
            'weight' => collect($this->indicators)->sum('weight'),
            'target' => collect($this->indicators)->sum('target'),
            'score' => collect($this->indicators)->sum('score'),
            'score_percentage' => collect($this->indicators)->sum('score_percentage'),
            'groups' => KpiGroupResource::collection($this->groups),
        ];
    }
}
