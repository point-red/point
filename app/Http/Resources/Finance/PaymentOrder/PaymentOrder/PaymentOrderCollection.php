<?php

namespace App\Http\Resources\Finance\PaymentOrder\PaymentOrder;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentOrderCollection extends ResourceCollection
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
