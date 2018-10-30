<?php

namespace App\Http\Resources\Purchasing\PurchaseDownPayment\PurchaseDownPayment;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseDownPaymentCollection extends ResourceCollection
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
