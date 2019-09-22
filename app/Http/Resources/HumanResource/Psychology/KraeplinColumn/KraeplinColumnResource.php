<?php

namespace App\Http\Resources\HumanResource\Psychology\KraeplinColumn;

use Illuminate\Http\Resources\Json\JsonResource;

class KraeplinColumnResource extends JsonResource
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
            'kraeplin_id' => $this->kraeplin_id,
            'current_first_number' => $this->current_first_number,
            'current_second_number' => $this->current_second_number,
            'correct' => $this->correct
        ];
    }
}
