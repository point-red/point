<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Service;

trait PriceListServiceRelation
{
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
