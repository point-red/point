<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountSubLedger;
use App\Model\Accounting\ChartOfAccountType;
use Illuminate\Database\Seeder;

class ChartOfAccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->chartAccountTypes();
        $this->chartAccountSubLedger();
    }

    private function chartAccountTypes()
    {
        $assets = [
            'cash',
            'bank',
            'note receivable',
            'inventory',
            'account receivable',
            'other account receivable',
            'fixed asset',
            'fixed asset depreciation',
            'other asset',
            'other asset amortization',
            'sales down payment',
        ];

        $assetsAlias = [
            'kas',
            'bank',
            'wesel tagih',
            'sediaan',
            'piutang usaha',
            'piutang lain lain',
            'aset tetap',
            'penyusutan aset tetap',
            'aset lain lain',
            'amortisasi aset lain',
            'uang muka penjualan',
        ];

        $liabilities = [
            'current liability',
            'note payable',
            'other current liability',
            'long term liability',
            'purchase down payment',
        ];

        $liabilitiesAlias = [
            'utang dagang',
            'wesel bayar',
            'utang lain lain',
            'utang jangka panjang',
            'uang muka pembelian',
        ];

        $equities = [
            'owner equity',
            'shareholder distribution',
            'retained earning',
        ];

        $equitiesAlias = [
            'modal pemilik',
            'dividen',
            'laba ditahan',
        ];

        $incomes = [
            'sales income',
            'other income',
        ];

        $incomesAlias = [
            'penjualan',
            'pendapatan non operasional',
        ];

        $expenses = [
            'cost of sales',
            'direct expense',
            'other expense',
        ];

        $expensesAlias = [
            'beban pokok penjualan',
            'beban operasional',
            'beban non operasional',
        ];

        for ($i = 0; $i < count($assets); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = $assets[$i];
            $chartOfAccountType->alias = $assetsAlias[$i];
            $chartOfAccountType->is_debit = true;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($liabilities); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = $liabilities[$i];
            $chartOfAccountType->alias = $liabilitiesAlias[$i];
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($equities); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = $equities[$i];
            $chartOfAccountType->alias = $equitiesAlias[$i];
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($incomes); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = $incomes[$i];
            $chartOfAccountType->alias = $incomesAlias[$i];
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($expenses); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = $expenses[$i];
            $chartOfAccountType->alias = $expensesAlias[$i];
            $chartOfAccountType->is_debit = true;
            $chartOfAccountType->save();
        }
    }

    private function chartAccountSubLedger()
    {
        $subLedger = [
            'inventory',
            'account payable',
            'purchase down payment',
            'account receivable',
            'sales down payment',
        ];

        $subLedgerAlias = [
            'sediaan',
            'utang usaha',
            'uang muka pembelian',
            'piutang usaha',
            'uang muka penjualan',
        ];

        for ($i = 0; $i < count($subLedger); $i++) {
            $chartOfAccountSubLedger = new ChartOfAccountSubLedger;
            $chartOfAccountSubLedger->name = $subLedger[$i];
            $chartOfAccountSubLedger->alias = $subLedgerAlias[$i];
            $chartOfAccountSubLedger->save();
        }
    }
}
