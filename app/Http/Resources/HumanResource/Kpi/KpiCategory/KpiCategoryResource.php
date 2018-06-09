<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiCategory;

use Illuminate\Http\Resources\Json\JsonResource;

class KpiCategoryResource extends JsonResource
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
