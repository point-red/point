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
            'candidate' => $this->when($request->input('expand') && strpos($request->input('includes'), 'candidate') !== false, $this->candidate),
            'column_duration' => $this->column_duration,
            'total_count' => $this->total_count,
            'total_correct' => $this->total_correct,
            'active_column_id' => $this->active_column_id,
            'active_column' => $this->when($request->input('expand') && strpos($request->input('includes'), 'active_column')  !== false, $this->active_column),
            'kraepelin_columns' => $this->when($request->input('expand') && strpos($request->input('includes'), 'kraepelin_columns')  !== false, $this->kraepelin_columns),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
