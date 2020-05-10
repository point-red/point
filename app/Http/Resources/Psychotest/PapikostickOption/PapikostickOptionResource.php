<?php

namespace App\Http\Resources\Psychotest\PapikostickOption;

use Illuminate\Http\Resources\Json\JsonResource;

class PapikostickOptionResource extends JsonResource
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
            'content' => $this->content,
            'question_id' => $this->question_id,
            'category_id' => $this->category_id,
            'question' => $this->when($request->input('expand') && strpos($request->input('includes'), 'question') !== false, $this->question),
            'category' => $this->when($request->input('expand') && strpos($request->input('includes'), 'category') !== false, $this->category),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString()
        ];
    }
}
