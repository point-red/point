<?php

namespace App\Http\Resources\Purchase\PurchaseDownPayment\PurchaseDownPayment;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseDownPaymentResource extends JsonResource
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
