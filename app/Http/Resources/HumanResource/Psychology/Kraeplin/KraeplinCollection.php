<?php

namespace App\Http\Resources\HumanResource\Psychology\Kraeplin;

use App\Model\HumanResource\Psychology\Kraeplin;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KraeplinCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Kraeplin $kraeplin) {
            return new KraeplinResource($kraeplin);
        });

        return parent::toArray($request);
    }
}
