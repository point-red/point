<?php

namespace App\Http\Resources\Psychotest\PapikostickQuestion;

use Illuminate\Http\Resources\Json\JsonResource;

class PapikostickQuestionResource extends JsonResource
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
            'number' => $this->number,
            'options' => $this->when($request->input('expand') && strpos($request->input('includes'), 'papikostick_options')  !== false, $this->papikostick_options),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
