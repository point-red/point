<?php

namespace App\Http\Resources\Accounting\CutOff;

use Illuminate\Http\Resources\Json\JsonResource;

class CutOffDetailResource extends JsonResource
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
            'chartOfAccount' => $this->chartOfAccount
        ]);

        return $resource;
    }
}
