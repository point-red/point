<?php

namespace App\Http\Resources\Sales\DeliveryNote\DeliveryNote;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryNoteResource extends JsonResource
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
