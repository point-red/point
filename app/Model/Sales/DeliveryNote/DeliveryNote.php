<?php

namespace App\Model\Sales\DeliveryNote;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $connection = 'tenant';

    protected $table = 'delivery_notes';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'delivery_order_id',
    ];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(deliveryOrder::class, 'delivery_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {

    }
}
