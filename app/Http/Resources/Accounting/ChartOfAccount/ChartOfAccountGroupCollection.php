<?php

namespace App\Http\Resources\Accounting\ChartOfAccount;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ChartOfAccountGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
