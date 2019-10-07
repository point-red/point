<?php

namespace App\Http\Resources\Psychotest\PapikostickQuestion;

use App\Model\Psychotest\PapikostickQuestion;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PapikostickQuestionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (PapikostickQuestion $papikostickQuestion) {
            return new PapikostickQuestionResource($papikostickQuestion);
        });

        return parent::toArray($request);
    }
}
