<?php

namespace App\Http\Resources\Accounting\BalanceSheet;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountGroupResource;

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
        $date = date('Y-m-d 23:59:59');

        if ($request->get('date')) {
            $date = date('Y-m-d 23:59:59', strtotime($request->get('date')));
        }

        return [
            'id' => $this->id,
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'alias' => $this->type->alias,
            ],
            'group' => new ChartOfAccountGroupResource($this->group),
            'number' => $this->number,
            'name' => $this->name,
            'alias' => $this->alias,
            'debit' => $this->totalDebit($date),
            'credit' => $this->totalCredit($date),
            'total' => $this->total($date),
        ];
    }
}
