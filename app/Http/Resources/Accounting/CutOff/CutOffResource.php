<?php

namespace App\Http\Resources\Accounting\CutOff;

use App\Http\Resources\ApiCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CutOffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'details' => new ApiCollection($this->details),
            'totalDebit' => $this->details->sum('debit'),
            'totalCredit' => $this->details->sum('credit'),
        ]);
    }
}
