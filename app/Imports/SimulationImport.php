<?php

namespace App\Imports;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use App\Model\Form;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SimulationImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        if (env('APP_ENV') == 'local') {
            DB::connection('tenant')->beginTransaction();
            foreach ($collection as $row) {
                $form = Form::where('number', $row['form'])->first();
                if (! $form) {
                    $form = new Form;
                    $form->date = $row['date'];
                    $form->number = $row['form'];
                    $form->created_by = 1;
                    $form->updated_by = 1;
                    $form->save();
                }

                $journal = new Journal;
                $journal->form_id = $form->id;
                $journal->chart_of_account_id = ChartOfAccount::where('alias', '=', $row['chart_of_account'])->first()->id;
                $journal->debit = $row['debit'] ?? 0;
                $journal->credit = $row['credit'] ?? 0;
                $journal->notes = $row['notes'];
                if ($row['sub_ledger']) {
                    $journal->journalable_type = $row['master'];
                    $journal->journalable_id = $row['master_id'];
                }
                $journal->save();
            }
            DB::connection('tenant')->commit();
        }
    }
}
