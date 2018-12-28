<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
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
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
