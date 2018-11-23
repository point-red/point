<?php

namespace App\Http\Resources\Purchase\PurchaseContract\PurchaseContract;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseContractResource extends JsonResource
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
