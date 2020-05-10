<?php

namespace App\Http\Resources\Psychotest\PositionCategory;

use Illuminate\Http\Resources\Json\JsonResource;

class PositionCategoryResource extends JsonResource
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
            'category_max' => $this->category_max,
            'category_min' => $this->category_min,
            'position_id' => $this->position_id,
            'category_id' => $this->category_id,
            'position' => $this->when($request->input('expand') && strpos($request->input('includes'), 'position') !== false, $this->position),
            'category' => $this->when($request->input('expand') && strpos($request->input('includes'), 'category') !== false, $this->category)
        ];
    }
}
