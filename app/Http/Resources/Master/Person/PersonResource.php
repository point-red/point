<?php

namespace App\Http\Resources\Master\Person;

use Illuminate\Http\Resources\Json\Resource;

class PersonResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
