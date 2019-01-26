<?php

namespace App\Http\Resources\Finance\Cash\Cash;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CashCollection extends ResourceCollection
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
