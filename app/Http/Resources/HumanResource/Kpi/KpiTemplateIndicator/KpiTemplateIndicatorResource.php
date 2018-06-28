<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreResource;

class KpiTemplateIndicatorResource extends JsonResource
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
            'kpi_template_group_id' => $this->kpi_template_group_id,
            'name' => $this->name,
            'weight' => $this->weight,
            'target' => $this->target,
            'scores' => new KpiTemplateScoreResource($this->scores),
        ];
    }
}
