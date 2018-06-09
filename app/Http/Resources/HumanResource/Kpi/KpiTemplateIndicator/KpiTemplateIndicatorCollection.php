<?php

namespace App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator;

use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiTemplateIndicatorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
