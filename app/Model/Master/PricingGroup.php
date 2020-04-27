<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\PricingGroupJoin;
use App\Traits\Model\Master\PricingGroupRelation;

class PricingGroup extends MasterModel
{
    use PricingGroupRelation, PricingGroupJoin;

    protected $connection = 'tenant';

    public static $alias = 'pricing_group';

    protected $fillable = [
        'label',
        'notes',
    ];
}
