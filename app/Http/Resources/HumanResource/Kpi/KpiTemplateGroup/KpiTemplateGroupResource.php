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
            'name' => $this->name,
            'weight' => collect($this->indicators)->sum('weight'),
            'target' => collect($this->indicators)->sum('target'),
            'indicators' => KpiTemplateIndicatorResource::collection($this->indicators)
        ];
    }
}
