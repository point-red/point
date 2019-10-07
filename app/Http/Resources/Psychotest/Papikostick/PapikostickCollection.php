<?php

namespace App\Http\Resources\Psychotest\Papikostick;

use App\Model\Psychotest\Papikostick;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PapikostickCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Papikostick $papikostick) {
            return new PapikostickResource($papikostick);
        });

        return parent::toArray($request);
    }
}
