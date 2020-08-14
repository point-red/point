<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup;

use App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorResource;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiTemplateGroupResource extends JsonResource
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
            'weight' => collect($this->indicators)->sum('weight'),
            'target' => collect($this->indicators)->sum('target'),
            'indicators' => KpiTemplateIndicatorResource::collection($this->indicators),
        ];
    }
}
