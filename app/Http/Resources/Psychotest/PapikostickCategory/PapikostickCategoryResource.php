<?php

namespace App\Http\Resources\Psychotest\PapikostickCategory;

use Illuminate\Http\Resources\Json\JsonResource;

class PapikostickCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'position_category' => $this->when($request->input('expand') && strpos($request->input('includes'), 'position_category') !== false, $this->position_category),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
