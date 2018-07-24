<?php

namespace App\Http\Resources\Project\Project;

use App\Model\Project\Project;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Project $project) {
            return new ProjectResource($project);
        });

        return parent::toArray($request);
    }
}
