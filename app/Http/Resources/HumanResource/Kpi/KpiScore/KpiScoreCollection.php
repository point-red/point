<?php
namespace App\Http\Resources\HumanResource\Kpi\KpiScore;

use App\Model\HumanResource\Kpi\KpiScore;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KpiScoreCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KpiScore $kpiScore) {
            return new KpiScoreResource($kpiScore);
        });
        
        return parent::toArray($request);
    }
}