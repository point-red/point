<?php

namespace App\Http\Resources\HumanResource\JobValue\JobValueCategory;

use App\Model\HumanResource\JobValue\JobValueCategory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JobValueCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (JobValueCategory $jobValueCategory) {
            return new JobValueCategoryResource($jobValueCategory);
        });

        return parent::toArray($request);
    }
}
