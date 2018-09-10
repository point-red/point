<?php

namespace App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ScaleWeightItemCollection extends ResourceCollection
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
