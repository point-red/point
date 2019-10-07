<?php

namespace App\Http\Resources\Psychotest\Papikostick;

use Illuminate\Http\Resources\Json\JsonResource;

class PapikostickResource extends JsonResource
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
            'candidate_id' => $this->candidate_id,
            'candidate' => $this->when($request->input('expand') && strpos($request->input('includes'), 'candidate') !== false, $this->candidate),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
