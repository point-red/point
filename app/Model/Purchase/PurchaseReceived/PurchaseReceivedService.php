<?php

namespace App\Model\Purchase\PurchaseReceived;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceivedService extends Model
{
    protected $connection = 'tenant';
    
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'quantity',
    ];

    protected $casts = [
        'quantity'  => 'double',
    ];

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
