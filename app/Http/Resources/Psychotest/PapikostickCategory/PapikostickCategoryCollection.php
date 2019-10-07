<?php

namespace App\Http\Resources\Psychotest\PapikostickCategory;

use App\Model\Psychotest\PapikostickCategory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PapikostickCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (PapikostickCategory $papikostickCategory) {
            return new PapikostickCategoryResource($papikostickCategory);
        });

        return parent::toArray($request);
    }
}
