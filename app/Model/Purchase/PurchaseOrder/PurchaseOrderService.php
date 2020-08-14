<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
use App\Model\TransactionModel;

class PurchaseOrderService extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'purchase_order_service';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_service_id',
        'service_id',
        'quantity',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
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
