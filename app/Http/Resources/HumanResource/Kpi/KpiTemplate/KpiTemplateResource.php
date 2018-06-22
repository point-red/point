<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplate;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource;

class KpiTemplateResource extends JsonResource
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
            'groups' => KpiTemplateGroupResource::collection($this->groups),
        ];
    }
}
