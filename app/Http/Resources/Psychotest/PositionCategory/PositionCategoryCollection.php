<?php

namespace App\Http\Resources\Psychotest\PositionCategory;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PositionCategoryCollection extends ResourceCollection
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
