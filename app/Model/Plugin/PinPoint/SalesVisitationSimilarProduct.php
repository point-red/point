<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class SalesVisitationSimilarProduct extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_similar_products';
}
