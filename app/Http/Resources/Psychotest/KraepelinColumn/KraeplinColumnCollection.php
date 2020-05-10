<?php

namespace App\Http\Resources\Psychotest\KraepelinColumn;

use App\Model\Psychotest\KraepelinColumn;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KraepelinColumnCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (KraepelinColumn $kraepelinColumn) {
            return new KraepelinColumnResource($kraepelinColumn);
        });
        
        return parent::toArray($request);
    }
}
