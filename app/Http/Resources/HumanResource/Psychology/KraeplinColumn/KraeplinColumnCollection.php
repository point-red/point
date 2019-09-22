<?php

namespace App\Http\Resources\HumanResource\Psychology\KraeplinColumn;

use App\Model\HumanResource\Psychology\KraeplinColumn;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KraeplinColumnCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KraeplinColumn $kraeplinColumn) {
            return new KraeplinColumnResource($kraeplinColumn);
        });
        
        return parent::toArray($request);
    }
}
