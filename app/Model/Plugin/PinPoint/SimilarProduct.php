<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class SimilarProduct extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_similar_products';
}
