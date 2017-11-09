<?php

namespace App\Http\Resources\Master\Warehouse;

use Illuminate\Http\Resources\Json\Resource;

class WarehouseResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
