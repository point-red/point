<?php

namespace App\Http\Resources\Accounting\ChartOfAccount;

use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountGroupResource extends JsonResource
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
