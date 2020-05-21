<?php

namespace App\Http\Resources\Plugin\PinPoint\SimilarProduct;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SimilarProductCollection extends ResourceCollection
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
