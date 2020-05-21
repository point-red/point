<?php

namespace App\Http\Resources\Master\PriceListItem;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceListItemResource extends JsonResource
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
