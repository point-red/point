<?php

namespace App\Http\Resources\Purchasing\PurchaseReturn\PurchaseReturn;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseReturnCollection extends ResourceCollection
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
