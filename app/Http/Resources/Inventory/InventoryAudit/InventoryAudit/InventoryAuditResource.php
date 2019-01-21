<?php

namespace App\Http\Resources\Inventory\InventoryAudit\InventoryAudit;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAuditResource extends JsonResource
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
