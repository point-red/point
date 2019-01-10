<?php

namespace App\Http\Resources\Sales\SalesContract\SalesContract;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SalesContractCollection extends ResourceCollection
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
