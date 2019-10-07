<?php

namespace App\Http\Resources\Psychotest\PapikostickResult;

use App\Model\Psychotest\PapikostickResult;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PapikostickResultCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (PapikostickResult $papikostickResult) {
            return new PapikostickResultResource($papikostickResult);
        });

        return parent::toArray($request);
    }
}
