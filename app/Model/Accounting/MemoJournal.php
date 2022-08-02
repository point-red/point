<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Exceptions\IsReferencedException;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\MemoJournalItem;
use App\Traits\Model\Accounting\MemoJournalJoin;

class MemoJournal extends TransactionModel
{
    use MemoJournalJoin;
    
    public static $morphName = 'MemoJournal';

    protected $connection = 'tenant';

    public static $alias = 'memo_journal';

    public $timestamps = false;

    public $defaultNumberPrefix = 'MJ';

    protected $fillable = ['id'];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(MemoJournalItem::class);
    }

    // public function isAllowedToUpdate()
    // {
    //     if ($this->receiveItem != null) {
    //         throw new IsReferencedException('Cannot edit form because referenced by transfer receive', $this->receiveItem);
    //     }
    // }

    // public function isAllowedToDelete()
    // {
    //     if ($this->receiveItem != null) {
    //         throw new IsReferencedException('Cannot edit form because referenced by transfer receive', $this->receiveItem);
    //     }
    // }

    public static function create($data)
    {
        $memoJournal = new self;
        $memoJournal->fill($data);
        
        $items = self::mapItems($data['items'] ?? []);
        
        $memoJournal->save();
        
        $memoJournal->items()->saveMany($items);
        
        $form = new Form;
        $form->saveData($data, $memoJournal);

        return $memoJournal;
    }

    private static function mapItems($items)
    {
        $array = [];
        foreach ($items as $item) {
            array_push($array, $item);
        }
        
        return array_map(function ($item) {
            $memoJournalItem = new MemoJournalItem;
            $memoJournalItem->fill($item);

            return $memoJournalItem;
        }, $array);
    }

    public static function updateJournal($memoJournal)
    {
        foreach ($memoJournal->items as $memoJournalItem) {

            $journal = new Journal;
            $journal->form_id = $memoJournal->form->id;
            $journal->journalable_type = $memoJournalItem->masterable_type;
            $journal->journalable_id = $memoJournalItem->masterable_id;
            $journal->chart_of_account_id = $memoJournalItem->chart_of_account_id;
            $journal->debit = $memoJournalItem->debit;
            $journal->credit = $memoJournalItem->credit;
            $journal->save();

        }
    }

}
