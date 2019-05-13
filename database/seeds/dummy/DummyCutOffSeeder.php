<?php

use Illuminate\Database\Seeder;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\Journal;
use Illuminate\Support\Facades\DB;
use App\Model\Accounting\CutOffDetail;

class DummyCutOffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('tenant')->beginTransaction();

        $fromDate = '2018-04-01 00:00:00';
        $untilDate = '2018-04-30 23:59:59';
        $increment = CutOff::where('date', '>=', $fromDate)->where('date', '<=', $untilDate)->count();

        $cutOff = new CutOff;
        $cutOff->date = '2018-04-30 23:59:59';
        $cutOff->number = 'CUTOFF/'.date('ym', strtotime($cutOff->date)).'/'.sprintf('%04d', ++$increment);
        $cutOff->save();

        $total = 100000000;
        $accumulation = 0;
        $chartOfAccounts = \App\Model\Accounting\ChartOfAccount::join('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type_id')
            ->where('chart_of_account_types.is_debit', true)
            ->where('chart_of_account_types.name', '!=', 'fixed asset depreciation')
            ->where('chart_of_account_types.name', '!=', 'other asset amortization')
            ->select('chart_of_accounts.*')
            ->get();
        $i = 0;
        foreach ($chartOfAccounts as $chartOfAccount) {
            $i++;
            if ($chartOfAccounts->count() === $i) {
                $value = $total - $accumulation;
            } else {
                $value = rand(0, $total / 20);
            }

            if ($accumulation + $value > $total) {
                $value = $total - $accumulation;
            }

            $accumulation += $value;

            $cutOffDetail = new CutOffDetail;
            $cutOffDetail->cut_off_id = $cutOff->id;
            $cutOffDetail->chart_of_account_id = $chartOfAccount->id;
            $cutOffDetail->debit = $value;
            $cutOffDetail->credit = 0;
            $cutOffDetail->save();

            $journal = new Journal;
            $journal->journalable_type = $cutOff::$morphName;
            $journal->journalable_id = $cutOff->id;
            $journal->date = $cutOff->date;
            $journal->chart_of_account_id = $chartOfAccount->id;
            $journal->debit = $cutOffDetail->debit;
            $journal->credit = $cutOffDetail->credit;
            $journal->save();
        }

        $total = 100000000;
        $accumulation = 0;
        $chartOfAccounts = \App\Model\Accounting\ChartOfAccount::join('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type_id')
            ->where('chart_of_account_types.is_debit', false)
            ->where('chart_of_account_types.name', '!=', 'owner equity')
            ->where('chart_of_account_types.name', '!=', 'shareholder distribution')
            ->where('chart_of_account_types.name', '!=', 'retained earning')
            ->select('chart_of_accounts.*')
            ->get();
        $i = 0;
        foreach ($chartOfAccounts as $chartOfAccount) {
            $i++;
            if ($chartOfAccounts->count() === $i) {
                $value = $total - $accumulation;
            } else {
                $value = rand(0, $total / 20);
            }

            if ($accumulation + $value > $total) {
                $value = $total - $accumulation;
            }

            $accumulation += $value;

            $cutOffDetail = new CutOffDetail;
            $cutOffDetail->cut_off_id = $cutOff->id;
            $cutOffDetail->chart_of_account_id = $chartOfAccount->id;
            $cutOffDetail->debit = 0;
            $cutOffDetail->credit = $value;
            $cutOffDetail->save();

            $journal = new Journal;
            $journal->journalable_type = CutOff::$morphName;
            $journal->journalable_id = $cutOff->id;
            $journal->date = $cutOff->date;
            $journal->chart_of_account_id = $chartOfAccount->id;
            $journal->debit = $cutOffDetail->debit;
            $journal->credit = $cutOffDetail->credit;
            $journal->save();
        }

        DB::connection('tenant')->commit();
    }
}
