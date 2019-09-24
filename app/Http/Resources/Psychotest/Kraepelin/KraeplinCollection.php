<?php

namespace App\Http\Resources\Psychotest\Kraepelin;

use App\Model\Psychotest\Kraepelin;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KraepelinCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Kraepelin $kraepelin) {
            return new KraepelinResource($kraepelin);
        });

        return parent::toArray($request);
    }
}
