<?php

namespace App\Http\Resources\HumanResource\JobValue\JobValueCategory;

use Illuminate\Http\Resources\Json\JsonResource;

class JobValueCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
