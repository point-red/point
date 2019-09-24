<?php

namespace App\Http\Resources\Psychotest\Kraepelin;

use Illuminate\Http\Resources\Json\JsonResource;

class KraepelinResource extends JsonResource
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
            'column_duration' => $this->column_duration,
            'total_count' => $this->total_count,
            'total_correct' => $this->total_correct,
            'active_column_id' => $this->active_column_id,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
