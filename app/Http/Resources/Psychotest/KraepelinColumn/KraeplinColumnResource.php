<?php

namespace App\Http\Resources\Psychotest\KraepelinColumn;

use Illuminate\Http\Resources\Json\JsonResource;

class KraepelinColumnResource extends JsonResource
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
            'kraepelin_id' => $this->kraepelin_id,
            'current_first_number' => $this->current_first_number,
            'current_second_number' => $this->current_second_number,
            'count' => $this->count,
            'correct' => $this->correct,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
