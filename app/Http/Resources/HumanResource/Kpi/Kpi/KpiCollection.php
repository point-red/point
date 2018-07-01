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

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        $kpis = Kpi::all();

        $dates = [];
        $scores = [];

        foreach ($kpis as $key => $kpi) {
            array_push( $dates, $kpi->date);
            array_push( $scores, $kpi->indicators->sum('score_percentage'));
        }

        return [
            'data_set' => [
                'dates' => $dates,
                'scores' => $scores
            ],
        ];
    }
}
