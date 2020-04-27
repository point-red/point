<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Service;

trait ServiceGroupRelation
{
    /**
     * get all of the services that are assigned this group.
     */
    public function services()
    {
        return $this->belongstomany(Service::class);
    }
}
