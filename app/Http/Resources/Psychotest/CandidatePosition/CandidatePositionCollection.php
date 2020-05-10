<?php

namespace App\Http\Resources\Psychotest\CandidatePosition;

use App\Model\Psychotest\CandidatePosition;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CandidatePositionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (CandidatePosition $candidatePosition) {
            return new CandidatePositionResource($candidatePosition);
        });

        return parent::toArray($request);
    }
}
