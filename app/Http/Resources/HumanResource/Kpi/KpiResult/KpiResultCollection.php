<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiResult;

use App\Model\HumanResource\Kpi\KpiResult;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiResultCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiResult $kpiResult) {
            return new KpiResultResource($kpiResult);
        });

        return parent::toArray($request);
    }
}
