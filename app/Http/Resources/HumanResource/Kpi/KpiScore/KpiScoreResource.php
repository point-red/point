<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiScore;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailResource;

class KpiScoreResource extends JsonResource
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
            'kpi_template_indicator_id' => $this->kpi_template_indicator_id,
            'details' => KpiScoreDetailResource::collection($this->details),
        ];
    }
}
