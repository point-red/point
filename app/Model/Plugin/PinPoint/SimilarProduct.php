<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\MasterModel;

class SimilarProduct extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'similar_product';

    protected $table = 'pin_point_similar_products';

    protected $fillable = [
        'name',
    ];
}
