<?php

namespace App\Http\Resources\Psychotest\Candidate;

use App\Model\Psychotest\Candidate;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CandidateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Candidate $candidate) {
            return new CandidateResource($candidate);
        });
        
        return parent::toArray($request);
    }
}
