<?php

namespace App\Model\Purchase\PurchaseReturn;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'purchase_return_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'converter' => 'double',
    ];
}
