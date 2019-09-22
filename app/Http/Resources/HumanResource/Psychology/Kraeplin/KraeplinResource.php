<?php

namespace App\Http\Resources\HumanResource\Psychology\Kraeplin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Psychology\Candidate\CandidateResource;
use App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnResource;

class KraeplinResource extends JsonResource
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
            'active_column_id' => $this->active_column_id
        ];
    }
}
