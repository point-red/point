<?php

namespace App\Model\Sales\DeliveryNote;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\TransactionModel;

class DeliveryNote extends TransactionModel
{
    protected $connection = 'tenant';

    protected $table = 'delivery_notes';

    public $timestamps = false;

    protected $fillable = [
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
        $deliveryOrder = DeliveryOrder::findOrFail($data['delivery_order_id']);
        // TODO add check if $salesOrder is canceled / rejected / archived

        $deliveryNote = new self;
        $deliveryNote->fill($data);
        $deliveryNote->customer_id = $deliveryOrder->customer->id;
        $deliveryNote->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $deliveryNote->id;
        $form->formable_type = self::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'DO{y}{m}{increment=4}',
            $deliveryNote->customer_id,
            null
        );
        $form->save();

        // TODO items is required and must be array
        $array = [];
        $items = $data['items'];
        foreach ($items as $item) {
            $deliveryNoteItem = new DeliveryOrderItem;
            $deliveryNoteItem->fill($item);
            $deliveryNoteItem->delivery_order_id = $deliveryNote->id;
            array_push($array, $deliveryNoteItem);
        }
        $deliveryNote->items()->saveMany($array);

        $deliveryOrder->updateIfDone();

        return $deliveryNote;
    }
}
