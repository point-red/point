<?php

namespace App\Http\Resources\Psychotest\PapikostickOption;

use App\Model\Psychotest\PapikostickOption;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PapikostickOptionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (PapikostickOption $papikostickOption) {
            return new PapikostickOptionResource($papikostickOption);
        });

        return parent::toArray($request);
    }
}
