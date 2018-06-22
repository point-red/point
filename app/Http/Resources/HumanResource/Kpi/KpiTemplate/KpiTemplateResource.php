<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplate;

use App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'groups' => KpiTemplateGroupResource::collection($this->groups)
        ];
    }
}
