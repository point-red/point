<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiGroup;

use App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiGroupResource extends JsonResource
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
            'kpi_template_id' => $this->kpi_template_id,
            'name' => $this->name,
            'weight' => collect($this->kpis)->sum('weight'),
            'target' => collect($this->kpis)->sum('target'),
            'indicators' => KpiResource::collection($this->kpis),
        ];
    }
}
