<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
use App\Model\Sales\SalesOrder\SalesOrder;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_service_id',
        'service_id',
        'service_name',
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
        'discount_value' => 'double',
        'discount_percent' => 'double',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
