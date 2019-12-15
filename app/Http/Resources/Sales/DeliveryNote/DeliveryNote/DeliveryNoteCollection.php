<?php

namespace App\Http\Resources\Sales\DeliveryNote\DeliveryNote;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DeliveryNoteCollection extends ResourceCollection
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
