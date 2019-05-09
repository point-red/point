<?php

namespace App\Model\Sales\SalesOrder;

use App\Model\Master\Service;
use App\Model\Master\Allocation;
use Illuminate\Database\Eloquent\Model;

class SalesOrderService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
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
