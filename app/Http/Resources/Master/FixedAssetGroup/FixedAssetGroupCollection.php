<?php

namespace App\Http\Resources\Master\FixedAssetGroup;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FixedAssetGroupCollection extends ResourceCollection
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
