<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiCategory;

use App\Model\HumanResource\Kpi\KpiCategory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiCategory $kpiCategory) {
            return (new KpiCategoryResource($kpiCategory));
        });

        return parent::toArray($request);
    }
}
