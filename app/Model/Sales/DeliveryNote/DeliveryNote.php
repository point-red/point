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
        $deliveryOrder = DeliveryOrder::findOrFail($data['delivery_order_id']);

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

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $deliveryNoteItem = new DeliveryOrderItem;
            $deliveryNoteItem->fill($item);
            $deliveryNoteItem->delivery_order_id = $deliveryNote->id;
            array_push($array, $deliveryNoteItem);
        }
        $deliveryNote->items()->saveMany($array);

        // Make form done when all item delivered
        $done = true;

        $deliveryOrderItemIds = array_column($deliveryOrder->items->toArray(), 'id');

        $tempArray = DeliveryNoteItem::whereIn('delivery_order_item_id', $deliveryOrderItemIds)
            ->join(DeliveryNote::getTableName(), DeliveryNote::getTableName().'.id', '=', 'delivery_order_items.delivery_order_id')
            ->join(Form::getTableName(), DeliveryNote::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->groupBy('delivery_order_item_id')
            ->select('delivery_order_items.delivery_order_item_id')
            ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
            ->where(function($query) {
                $query->where(Form::getTableName().'.canceled', false)
                    ->orWhereNull(Form::getTableName().'.canceled');
            })->where(function($query) {
                $query->where(Form::getTableName().'.approved', true)
                    ->orWhereNull(Form::getTableName().'.approved');
            })->get();

        $quantityDeliveredItems = [];

        foreach ($tempArray as $value) {
            $quantityDeliveredItems[$value['delivery_order_item_id']] = $value['sum_delivered'];
        }

        foreach ($deliveryOrder->items as $deliveryOrderItem) {
            $quantityDelivered = $quantityDeliveredItems[$deliveryOrderItem->id] ?? 0;
            if ($deliveryOrderItem->quantity - $quantityDelivered > 0) {
                $done = false;
                break;
            }
        }

        if ($done == true) {
            $deliveryOrder->form->done = true;
            $deliveryOrder->form->save();
        }

        $deliveryNote->form();

        return $deliveryNote;
    }
}
