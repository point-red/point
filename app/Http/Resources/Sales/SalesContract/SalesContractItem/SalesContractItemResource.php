<?php

namespace App\Http\Resources\Sales\SalesContract\SalesContractItem;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesContractItemResource extends JsonResource
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
