<?php

use App\Model\Accounting\ChartOfAccount;
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
    }

    private function chartAccountTypes()
    {
        $assets = [
            'cash',
            'cash-bank transfer',
            'bank',
            'supplies',
            'note receivable',
            'input vat',
            'inventory',
            'account receivable',
            'account receivable of management',
            'account receivable of employee',
            'other account receivable',
            'purchase down payment',
            'fixed asset',
            'fixed asset depreciation',
            'other asset',
            'other asset amortization',
        ];

        $assetsAlias = [
            'kas',
            'ayat silang kas bank',
            'bank',
            'perlengkapan',
            'wesel tagih',
            'ppn masukan',
            'sediaan',
            'piutang usaha',
            'piutang manajemen',
            'piutang karyawan',
            'piutang lain lain',
            'uang muka pembelian',
            'aset tetap',
            'penyusutan aset tetap',
            'aset lain lain',
            'amortisasi aset lain',
        ];

        $liabilities = [
            'current liability',
            'note payable',
            'other current liability',
            'sales down payment',
            'output vat',
            'long term liability',
        ];

        $liabilitiesAlias = [
            'utang dagang',
            'wesel bayar',
            'utang lain lain',
            'uang muka penjualan',
            'ppn keluaran',
            'utang jangka panjang',
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
            'factory overhead cost',
        ];

        $expensesAlias = [
            'beban pokok penjualan',
            'beban operasional',
            'beban non operasional',
            'biaya overhead pabrik',
        ];

        for ($i = 0; $i < count($assets); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($assets[$i]);
            $chartOfAccountType->alias = strtoupper($assetsAlias[$i]);
            $chartOfAccountType->is_debit = true;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($liabilities); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($liabilities[$i]);
            $chartOfAccountType->alias = strtoupper($liabilitiesAlias[$i]);
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($equities); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($equities[$i]);
            $chartOfAccountType->alias = strtoupper($equitiesAlias[$i]);
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($incomes); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($incomes[$i]);
            $chartOfAccountType->alias = strtoupper($incomesAlias[$i]);
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($expenses); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($expenses[$i]);
            $chartOfAccountType->alias = strtoupper($expensesAlias[$i]);
            $chartOfAccountType->is_debit = true;
            $chartOfAccountType->save();
        }
    }
}
