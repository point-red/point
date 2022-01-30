<?php

namespace App\Traits\Model\Master;

use App\Model\Master\FixedAsset;

trait FixedAssetGroupRelation
{
    /**
     * get all of the items that are assigned this group.
     */
    public function fixedAssets()
    {
        return $this->belongstomany(FixedAsset::class);
    }
}
