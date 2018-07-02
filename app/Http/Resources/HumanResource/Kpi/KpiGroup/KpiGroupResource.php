<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiGroup;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorResource;

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
            'weight' => collect($this->indicators)->sum('weight'),
            'target' => collect($this->indicators)->sum('target'),
            'score' => collect($this->indicators)->sum('score'),
            'score_percentage' => collect($this->indicators)->sum('score_percentage'),
            'indicators' => KpiIndicatorResource::collection($this->indicators),
        ];
    }
}
