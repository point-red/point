<?php

namespace App\Http\Resources\Purchase\PurchaseRequest\PurchaseRequest;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseRequestCollection extends ResourceCollection
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
