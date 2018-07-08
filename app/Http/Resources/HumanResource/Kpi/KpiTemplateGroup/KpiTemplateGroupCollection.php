<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup;

use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiTemplateGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiTemplateGroup $kpiTemplateGroup) {
            return new KpiTemplateGroupResource($kpiTemplateGroup);
        });

        return parent::toArray($request);
    }
}
