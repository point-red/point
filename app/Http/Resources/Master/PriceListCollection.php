<?php

namespace App\Http\Resources\Master;

use App\Model\Master\PricingGroup;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PriceListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'price_lists' => $this->collection,
                'groups' => PricingGroup::all(),
            ],
        ];
    }
}
