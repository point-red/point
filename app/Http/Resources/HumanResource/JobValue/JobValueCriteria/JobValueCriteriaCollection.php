<?php

namespace App\Http\Resources\HumanResource\JobValue\JobValueCriteria;

use App\Model\HumanResource\JobValue\JobValueCriteria;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JobValueCriteriaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (JobValueCriteria $jobValueCriteria) {
            return new EmployeeJobLocationResource($jobValueCriteria);
        });

        return parent::toArray($request);
    }
}
