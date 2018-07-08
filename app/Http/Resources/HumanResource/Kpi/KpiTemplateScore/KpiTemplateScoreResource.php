<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplateScore;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;

class KpiTemplateScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $indicator = KpiTemplateIndicator::findOrFail($this->kpi_template_indicator_id);

        return [
            'id' => $this->id,
            'kpi_template_indicator_id' => $indicator->id,
            'kpi_template_group_id' => $indicator->kpi_template_group_id,
            'kpi_template_id' => $indicator->group->kpi_template_id,
            'description' => $this->description,
            'score' => $this->score,
        ];
    }
}
