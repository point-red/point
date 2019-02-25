<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiScore;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return parent::toArray($request);
    }
}
