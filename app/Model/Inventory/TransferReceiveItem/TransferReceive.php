<?php

namespace App\Model\Inventory\TransferReceiveItem;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Inventory\TransferSendItem\TransferSend;

class TransferReceive extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_from',
        'warehouse_to',
        'note',
        'transfer_id',
    ];

    public $defaultNumberPrefix = 'RC';

    public function transferSend()
    {
        return $this->belongsTo(TransferSend::class, 'transfer_id');
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(TransferReceiveItem::class, 'receive_id');
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
        $receive = new self;
        $receive->fill($data['form']);
        $receive->save();

        $form = new Form;
        $form->fillData($data['form'], $receive);

        $array = [];
        $array_inv = [];
        $array_journal_sediaan = [];
        $array_journal_perjalanan = [];
        $items = $data['items'];

        $chart_sediaan = ChartOfAccount::where('name', 'sediaan bahan baku')->select('id')->firstOrFail();
        $chart_perjalanan = ChartOfAccount::where('name', 'sediaan dalam perjalanan')->select('id')->firstOrFail();

        foreach ($items as $item) {
            $receiveItem = new TransferReceiveItem;
            $receiveItem->fill(['quantity'=>$item['quantity'], 'item_id'=>$item['item']]);
            array_push($array, $receiveItem);

            $array_inv[] = [
                'quantity'=>$item['quantity'],
                'item_id'=>$item['item'],
                'warehouse_id'=>$data['form']['warehouse_to'],
                'form_id'=>$form->id,
            ];

            $array_journal_sediaan[] = [
                'form_id' => $form->id,
                'chart_of_account_id' => $chart_sediaan->id,
                'journalable_type' => get_class($receive),
                'journalable_id' => $receive->id,
                'debit' => $item['quantity'],
            ];
            $array_journal_perjalanan[] = [
                'form_id' => $form->id,
                'chart_of_account_id' => $chart_perjalanan->id,
                'journalable_type' => get_class($receive),
                'journalable_id' => $receive->id,
                'credit' => $item['quantity'],
            ];
        }

        $receive->transferSend->form->done = true;
        $receive->transferSend->form->save();

        $receive->items()->saveMany($array);
        Inventory::insert($array_inv);
        Journal::insert($array_journal_sediaan);
        Journal::insert($array_journal_perjalanan);

        return $receive;
    }
}
