<?php

namespace App\Http\Resources\HumanResource\Kpi\Kpi;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreResource;

class KpiIndicatorResource extends JsonResource
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
            'kpi_group_id' => $this->kpi_group_id,
            'name' => $this->name,
            'weight' => $this->weight,
            'target' => $this->target,
            'score' => $this->score,
            'score_percentage' => $this->score_percentage,
            'score_description' => $this->score_description,
            'automated_id' => $this->automated_id,
            'scores' => KpiScoreResource::collection($this->scores->sortBy('score')),
        ];
    }
}
