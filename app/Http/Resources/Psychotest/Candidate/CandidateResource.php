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
            'position_id' => $this->position_id,
            'position' => $this->when($request->input('expand') && strpos($request->input('includes'), 'position') !== false, $this->position),

            'kraepelin' => $this->when($request->input('expand') && strpos($request->input('includes'), 'kraepelin') !== false, $this->kraepelin),
            'papikostick' => $this->when($request->input('expand') && strpos($request->input('includes'), 'papikostick') !== false, $this->papikostick),

            'password' => $this->password,
            'is_password_used' => boolval($this->is_password_used),

            'is_kraepelin_started' => boolval($this->is_kraepelin_started),
            'is_kraepelin_finished' => boolval($this->is_kraepelin_finished),

            'is_papikostick_started' => boolval($this->is_papikostick_started),
            'current_papikostick_index' => $this->current_papikostick_index,
            'is_papikostick_finished' => boolval($this->is_papikostick_finished),

            'level' => $this->level,
            'ktp_number' => $this->ktp_number,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth,
            'sex' => $this->sex,
            'religion' => $this->religion,
            'marital_status' => $this->marital_status,

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
