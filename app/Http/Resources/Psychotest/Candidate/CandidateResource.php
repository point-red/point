<?php

namespace App\Http\Resources\Psychotest\Candidate;

use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
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
            'phone' => $this->phone,
            'password' => $this->password,
            'is_password_used' => boolval($this->is_password_used),

            'is_kraepelin_started' => boolval($this->is_kraepelin_started),
            'is_kraepelin_finished' => boolval($this->is_kraepelin_finished),

            'is_papikostick_started' => boolval($this->is_papikostick_started),
            'current_papikostick_index' => $this->current_papikostick_index,
            'is_papikostick_finished' => boolval($this->is_papikostick_finished),

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
