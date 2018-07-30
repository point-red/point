<?php

namespace App\Http\Resources\Accounting\CutOff;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CutOffCollection extends ResourceCollection
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
