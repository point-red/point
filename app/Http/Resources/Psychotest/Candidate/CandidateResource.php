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
            'is_kraepelin_filled' => boolval($this->is_kraepelin_filled),
            'is_papikostick_filled' => boolval($this->is_papikostick_filled),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
