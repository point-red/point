<?php

namespace App\Http\Resources\Plugin\PinPoint\Report\Performance;

use App\Model\Master\Item;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PerformanceCollection extends ResourceCollection
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
                'reports' => $this->collection,
                'items' => Item::select('id', 'name')->get(),
            ],
        ];
    }
}
