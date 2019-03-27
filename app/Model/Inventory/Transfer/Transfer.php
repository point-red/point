<?php

namespace App\Model\Inventory\Transfer;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Accounting\ChartOfAccount;

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

    public function warehouseFrom()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from');
    }

    public function warehouseTo()
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
        $array_journal_sediaan = [];
        $array_journal_perjalanan = [];
        $items = $data['items'];

        $chart_sediaan = ChartOfAccount::where('name', 'sediaan bahan baku')->select('id')->firstOrFail();
        $chart_perjalanan = ChartOfAccount::where('name', 'sediaan dalam perjalanan')->select('id')->firstOrFail();

        foreach ($items as $item) {

            $transferItem = new TransferItem;
            $transferItem->fill(['quantity'=>$item['quantity'], 'item_id'=>$item['item']]);
            array_push($array, $transferItem);

            $array_inv[] = [
                'quantity'=>$item['quantity'],
                'item_id'=>$item['item'],
                'warehouse_id'=>$data['form']['warehouse_from'],
                'form_id'=>$form->id,
            ];

            $array_journal_sediaan[] = [
                'form_id' => $form->id,
                'chart_of_account_id' => $chart_sediaan->id,
                'journalable_type' => get_class($transfer),
                'journalable_id' => $transfer->id,
                'credit' => $item['quantity'],
            ];
            $array_journal_perjalanan[] = [
                'form_id' => $form->id,
                'chart_of_account_id' => $chart_perjalanan->id,
                'journalable_type' => get_class($transfer),
                'journalable_id' => $transfer->id,
                'debit' => $item['quantity'],
            ];
        }

        $transfer->items()->saveMany($array);
        Inventory::insert($array_inv);
        Journal::insert($array_journal_sediaan);
        Journal::insert($array_journal_perjalanan);

        return $transfer;
    }
}
