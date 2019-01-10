<?php

namespace App\Http\Resources\Sales\SalesContract\SalesContractItem;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SalesContractItemCollection extends ResourceCollection
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
