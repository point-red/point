<?php

namespace App\Http\Resources\Plugin\PinPoint\SimilarProduct;

use Illuminate\Http\Resources\Json\JsonResource;

class SimilarProductResource extends JsonResource
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
