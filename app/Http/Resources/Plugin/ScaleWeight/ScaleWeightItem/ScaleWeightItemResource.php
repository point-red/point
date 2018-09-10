<?php

namespace App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem;

use Illuminate\Http\Resources\Json\JsonResource;

class ScaleWeightItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
