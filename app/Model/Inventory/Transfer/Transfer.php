<?php

namespace App\Model\Inventory\Transfer;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Inventory\Transfer\TransferItem;

class Transfer extends TransactionModel
{
    protected $connection = 'tenant';

    // protected $table = 'delivery_orders';

    // public $timestamps = false;

    // protected $fillable = [
    //     'warehouse_id',
    //     'sales_order_id',
    //     'billing_address',
    //     'billing_phone',
    //     'billing_email',
    //     'shipping_address',
    //     'shipping_phone',
    //     'shipping_email',
    // ];

    public $defaultNumberPrefix = 'TR';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {

        $transfer = new self;
        $transfer->fill($data);
        $transfer->save();

        $form = new Form;
        $form->fillData($data, $transfer);

        // TODO items is required and must be array
        $array = [];
        $items = $data['items'];

        foreach ($items as $item) {

            $transferItem = new TransferItem;
            $transferItem->fill($item);

            array_push($array, $transferItem);
        }
        $transfer->items()->saveMany($array);

        return $transfer;
    }
}
