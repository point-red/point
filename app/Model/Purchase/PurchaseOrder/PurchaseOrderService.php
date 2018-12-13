<?php

namespace App\Model\Purchase\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'quantity',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'description',
        'allocation_id',
    ];

    protected $casts = [
        'quantity'         => 'double',
        'price'            => 'double',
        'discount_percent' => 'double',
        'discount_value'   => 'double',
        'converter'        => 'double',
    ];

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
