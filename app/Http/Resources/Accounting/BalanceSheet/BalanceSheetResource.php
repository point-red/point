<?php

namespace App\Http\Resources\Accounting\BalanceSheet;

use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountGroupResource;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountTypeResource;
use App\Model\Accounting\Journal;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceSheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'alias' => $this->type->alias
            ],
            'group' => new ChartOfAccountGroupResource($this->group),
            'number' => $this->number,
            'name' => $this->name,
            'alias' => $this->alias,
            'debit' => $this->totalDebit(),
            'credit' => $this->totalCredit(),
            'total' => $this->total()
        ];
    }
}
