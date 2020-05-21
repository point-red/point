<?php

namespace App\Http\Resources\Sales\SalesContract\SalesContract;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesContractResource extends JsonResource
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
