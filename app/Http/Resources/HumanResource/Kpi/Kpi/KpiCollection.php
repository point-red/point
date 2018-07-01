<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiCategory;

use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Kpi $kpi) {
            return new KpiResource($kpi);
        });

        return parent::toArray($request);
    }
}
