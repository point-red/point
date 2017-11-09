<?php

namespace App\Http\Resources\Master\PersonCategory;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
