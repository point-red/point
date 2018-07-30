<?php

namespace App\Http\Resources\Accounting\CutOff;

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
        $resource = array_merge(parent::toArray($request), [
            'details' => new CutOffDetailCollection($this->details),
            'totalDebit' => $this->details->sum('debit'),
            'totalCredit' => $this->details->sum('credit'),
        ]);

        return $resource;
    }
}
