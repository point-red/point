<?php

namespace App\Http\Resources\Inventory\InventoryAudit\InventoryAudit;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryAuditCollection extends ResourceCollection
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
