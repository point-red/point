<?php

namespace App\Http\Resources\Accounting\BalanceSheet;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BalanceSheetCollection extends ResourceCollection
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
