<?php

namespace App\Http\Resources\Accounting\BalanceSheet;

use App\Model\Accounting\ChartOfAccount;
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
        $this->collection->transform(function (ChartOfAccount $chartOfAccount) {
            return new BalanceSheetResource($chartOfAccount);
        });

        return parent::toArray($request);
    }
}
