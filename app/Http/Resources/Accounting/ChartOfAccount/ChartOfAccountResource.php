<?php

namespace App\Http\Resources\Accounting\ChartOfAccount;

use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
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
            'type' => new ChartOfAccountTypeResource($this->type),
            'group' => new ChartOfAccountGroupResource($this->group),
            'number' => $this->number,
            'name' => $this->name,
            'alias' => $this->alias,
        ];
    }
}
