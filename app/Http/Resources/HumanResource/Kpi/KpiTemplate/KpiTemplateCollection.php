<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplate;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiTemplateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiTemplate $kpiTemplate) {
            return (new KpiTemplateResource($kpiTemplate));
        });

        return parent::toArray($request);
    }
}
