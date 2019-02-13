<?php

namespace App\Http\Resources\Purchase\PurchaseReceived\PurchaseReceived;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReceivedResource extends JsonResource
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
