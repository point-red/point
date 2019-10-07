<?php

namespace App\Http\Resources\Psychotest\PapikostickResult;

use Illuminate\Http\Resources\Json\JsonResource;

class PapikostickResultResource extends JsonResource
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
            'total' => $this->total,
            'papikostick_id' => $this->papikostick_id,
            'category_id' => $this->category_id,
            'papikostick' => $this->when($request->input('expand') && strpos($request->input('includes'), 'papikostick') !== false, $this->papikostick),
            'category' => $this->when($request->input('expand') && strpos($request->input('includes'), 'category') !== false, $this->category),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
