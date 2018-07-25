<?php

use App\Accounting\ChartOfAccountType;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->chartAccountTypes();
        $this->chartOfAccount();
    }

    private function chartAccountTypes()
    {
        $assets = [
            'cash',
            'bank',
            'cheque',
            'credit card',
            'inventory',
            'account receivable',
            'other account receivable',
            'fixed asset',
            'other asset'
        ];

        $assetsAlias = [
            'kas',
            'bank',
            'wesel',
            'kartu kredit',
            'sediaan',
            'piutang usaha',
            'piutang lain lain',
            'aset tetap',
            'asset lain lain'
        ];

        $liabilities = [
            'current liability',
            'other current liability',
            'long term liability',
            'equity',
        ];

        $liabilitiesAlias = [
            'utang dagang',
            'utang lain lain',
            'utang jangka panjang',
            'modal',
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

    private function chartOfAccount()
    {
        $cash = ['kas besar', 'kas kecil', 'pos silang'];
        $bank = ['bank bca', 'bank mandiri'];
        $cheque = [];
        $creditCard = [];
        $inventory = ['sediaan bahan baku', 'sediaan bahan pembantu', 'sediaan bahan kemasan'];
        $accountReceivable = ['piutang dagang'];
        $otherAccountReceivable = [];
        $fixedAsset = ['tanah', 'bangunan', 'mesin'];
        $otherAsset = ['investasi'];
        $currentLiability = ['utang dagang', 'utang angkutan'];
        $otherCurrentLiability = [];
        $longTermLiability = [];
        $equity = ['modal'];
        $salesIncome = ['penjualan', 'potongan penjualan'];
        $otherIncome = ['pendapatan bunga bank', 'pendapatan (beban) selisih pembayaran'];
        $costOfSales = ['beban pokok penjualan', 'angkutan'];
        $directExpense = ['gaji dan tunjangan karyawan kantor', 'konsumsi', 'listrik', 'air'];
        $otherExpense = ['beban bunga leasing', 'beban (pendapatan) selisih pembayaran'];

        for ($i = 0; $i < count($cash); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'cash')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $cash[$i];
            $chartOfAccount->alias = $cash[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($bank); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'bank')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $bank[$i];
            $chartOfAccount->alias = $bank[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($cheque); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'cheque')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $cheque[$i];
            $chartOfAccount->alias = $cheque[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($creditCard); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'credit card')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $creditCard[$i];
            $chartOfAccount->alias = $creditCard[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($inventory); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'inventory')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $inventory[$i];
            $chartOfAccount->alias = $inventory[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($accountReceivable); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'account receivable')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $accountReceivable[$i];
            $chartOfAccount->alias = $accountReceivable[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherAccountReceivable); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other account receivable')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $otherAccountReceivable[$i];
            $chartOfAccount->alias = $otherAccountReceivable[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($fixedAsset); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'fixed asset')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $fixedAsset[$i];
            $chartOfAccount->alias = $fixedAsset[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherAsset); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other asset')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $otherAsset[$i];
            $chartOfAccount->alias = $otherAsset[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($currentLiability); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'current liability')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $currentLiability[$i];
            $chartOfAccount->alias = $currentLiability[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherCurrentLiability); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other current liability')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $otherCurrentLiability[$i];
            $chartOfAccount->alias = $otherCurrentLiability[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($longTermLiability); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'long term liability')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $longTermLiability[$i];
            $chartOfAccount->alias = $longTermLiability[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($equity); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'equity')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $equity[$i];
            $chartOfAccount->alias = $equity[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($salesIncome); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'sales income')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $salesIncome[$i];
            $chartOfAccount->alias = $salesIncome[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherIncome); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other income')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $otherIncome[$i];
            $chartOfAccount->alias = $otherIncome[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($costOfSales); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'cost of sales')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $costOfSales[$i];
            $chartOfAccount->alias = $costOfSales[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($directExpense); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'direct expense')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $directExpense[$i];
            $chartOfAccount->alias = $directExpense[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherExpense); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other expense')->first()->id;
            $chartOfAccount->number = null;
            $chartOfAccount->name = $otherExpense[$i];
            $chartOfAccount->alias = $otherExpense[$i];
            $chartOfAccount->save();
        }
    }
}
