<?php

namespace App\Http\Resources\Pos\PosBill;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PosBillCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
