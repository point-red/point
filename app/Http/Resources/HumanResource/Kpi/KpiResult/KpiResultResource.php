<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiResult;

use Illuminate\Http\Resources\Json\JsonResource;

class KpiResultResource extends JsonResource
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
