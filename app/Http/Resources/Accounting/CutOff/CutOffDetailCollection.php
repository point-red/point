<?php

namespace App\Http\Resources\Accounting\CutOff;

use App\Model\Accounting\CutOffDetail;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CutOffDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (CutOffDetail $cutOffDetail) {
            return new CutOffDetailResource($cutOffDetail);
        });

        return parent::toArray($request);
    }
}
