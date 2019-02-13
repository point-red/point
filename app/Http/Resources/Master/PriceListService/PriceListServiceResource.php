<?php

namespace App\Http\Resources\Master\PriceListService;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceListServiceResource extends JsonResource
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
