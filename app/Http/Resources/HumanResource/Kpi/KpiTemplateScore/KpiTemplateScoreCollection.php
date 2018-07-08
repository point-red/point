<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplateScore;

use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiTemplateScoreCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiTemplateScore $kpiTemplateScore) {
            return new KpiTemplateScoreResource($kpiTemplateScore);
        });

        return parent::toArray($request);
    }
}
