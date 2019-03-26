<?php

namespace App\Model\Inventory\Transfer;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Inventory\Transfer\TransferItem;
use App\Model\Inventory\Inventory;

class Transfer extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_from',
        'warehouse_to',
        'note',
    ];

    public $defaultNumberPrefix = 'TR';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function warehouse_from()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from');
    }

    public function warehouse_to()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to');
    }

    public static function create($data)
    {
        
        $transfer = new self;
        $transfer->fill($data['form']);
        $transfer->save();

        $form = new Form;
        $form->fillData($data['form'], $transfer);
        
        $array = [];
        $array_inv = [];
        $items = $data['items'];

        foreach ($items as $item) {

            $transferItem = new TransferItem;
            $transferItem->fill( ['quantity'=>$item['quantity'], 'item_id'=>$item['item']] );
            array_push($array, $transferItem);

            $array_inv[] = [
                'quantity'=>$item['quantity'], 
                'item_id'=>$item['item'],
                'warehouse_id'=>$data['form']['warehouse_from'],
                'form_id'=>$form->id,
            ];
        }
        $transfer->items()->saveMany($array);
        
        Inventory::insert($array_inv);

        return $transfer;
    }
}
