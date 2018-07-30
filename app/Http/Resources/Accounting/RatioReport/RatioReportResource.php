<?php

namespace App\Http\Resources\Accounting\RatioReport;

use Illuminate\Http\Resources\Json\JsonResource;

class RatioReportResource extends JsonResource
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
