<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiIndicator;

use App\Model\HumanResource\Kpi\KpiIndicator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiIndicatorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiIndicator $kpiIndicator) {
            return new KpiIndicatorResource($kpiIndicator);
        });

        return parent::toArray($request);
    }
}
