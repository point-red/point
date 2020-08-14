<?php

namespace App\Http\Resources\Manufacture\ManufactureFormula;

use Illuminate\Http\Resources\Json\JsonResource;

class ManufactureFormulaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
