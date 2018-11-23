<?php

namespace App\Http\Resources\Purchase\PurchaseInvoice\PurchaseInvoice;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseInvoiceCollection extends ResourceCollection
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
