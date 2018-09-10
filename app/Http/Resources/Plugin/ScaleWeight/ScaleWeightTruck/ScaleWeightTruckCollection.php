<?php

namespace App\Http\Resources\Plugin\ScaleWeight\ScaleWeightTruck;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ScaleWeightTruckCollection extends ResourceCollection
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
