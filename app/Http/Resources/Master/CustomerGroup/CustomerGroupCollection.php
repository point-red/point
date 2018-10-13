<?php

namespace App\Http\Resources\Master\CustomerGroup;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerGroupCollection extends ResourceCollection
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
