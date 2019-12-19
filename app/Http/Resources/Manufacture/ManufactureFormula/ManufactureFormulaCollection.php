<?php

namespace App\Http\Resources\Manufacture\ManufactureFormula;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ManufactureFormulaCollection extends ResourceCollection
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
