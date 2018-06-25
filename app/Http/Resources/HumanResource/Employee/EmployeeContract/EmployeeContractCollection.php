<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeContract;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeContractCollection extends ResourceCollection
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
