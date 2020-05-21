<?php

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
            ['CASH','KAS'],
            ['CASH-BANK TRANSFER', 'AYAT SILANG KAS BANK'],
            ['BANK', 'BANK'],
            ['SUPPLIES', 'PERLENGKAPAN'],
            ['INVENTORY', 'PERSEDIAAN'],
            ['NOTE RECEIVABLE', 'WESEL TAGIH'],
            ['ACCOUNT RECEIVABLE', 'PIUTANG USAHA'],
            ['ACCOUNT RECEIVABLE OF MANAGEMENT', 'PIUTANG MANAJEMEN'],
            ['ACCOUNT RECEIVABLE OF EMPLOYEE', 'PIUTANG KARYAWAN'],
            ['OTHER ACCOUNT RECEIVABLE', 'PIUTANG LAIN'],
            ['PURCHASE DOWN PAYMENT', 'UANG MUKA PEMBELIAN'],
            ['INCOME TAX RECEIVABLE', 'PPN MASUKAN'],
            ['OTHER CURRENT ASSET', 'ASET LANCAR LAIN-LAIN'],
            ['FIXED ASSET', 'ASET TETAP'],
            ['FIXED ASSET DEPRECIATION', 'DEPRESIASI ASET TETAP'],
            ['OTHER ASSETS', 'ASET LAIN'],
            ['OTHER ASSETS AMORTIZATION', 'AMORTISASI ASET LAIN'],
        ];

        $liabilities = [
            ['NOTE PAYABLE', 'WESEL BAYAR'],
            ['ACCOUNT PAYABLE', 'HUTANG USAHA'],
            ['SALES DOWN PAYMENT', 'UANG MUKA PENJUALAN'],
            ['INCOME TAX PAYABLE', 'PPN KELUARAN'],
            ['OTHER CURRENT LIABILITY', 'LIABILITAS JANGKA PENDEK LAIN-LAIN'],
            ['LONG TERM LIABILITY', 'LIABILITAS JANGKA PANJANG'],
        ];

        $equities = [
            ['OWNER EQUITY', 'MODAL PEMILIK'],
            ['SHAREHOLER DISTRIBUTION', 'DISTRIBUSI PEMEGANG SAHAM'],
            ['RETAINED EARNING', 'LABA DITAHAN'],
            ['NET INCOME', 'NET INCOME'],
        ];

        $incomes = [
            ['SALES INCOME', 'PENDAPATAN PENJUALAN'],
            ['OTHER INCOME', 'PENDAPATAN LAIN-LAIN'],
        ];

        $expenses = [
            ['COST OF SALES', 'BEBAN POKOK PENJUALAN'],
            ['DIRECT EXPENSE', 'BEBAN OPERASIONAL'],
            ['OTHER EXPENSE', 'BEBAN NON OPERASIONAL'],
            ['FACTORY OVERHEAD COST', 'BIAYA OVERHEAD PABRIK'],
        ];

        for ($i = 0; $i < count($assets); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($assets[$i][0]);
            $chartOfAccountType->alias = strtoupper($assets[$i][1]);
            $chartOfAccountType->is_debit = true;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($liabilities); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($liabilities[$i][0]);
            $chartOfAccountType->alias = strtoupper($liabilities[$i][1]);
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($equities); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($equities[$i][0]);
            $chartOfAccountType->alias = strtoupper($equities[$i][1]);
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($incomes); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($incomes[$i][0]);
            $chartOfAccountType->alias = strtoupper($incomes[$i][1]);
            $chartOfAccountType->is_debit = false;
            $chartOfAccountType->save();
        }

        for ($i = 0; $i < count($expenses); $i++) {
            $chartOfAccountType = new ChartOfAccountType;
            $chartOfAccountType->name = strtoupper($expenses[$i][0]);
            $chartOfAccountType->alias = strtoupper($expenses[$i][1]);
            $chartOfAccountType->is_debit = true;
            $chartOfAccountType->save();
        }
    }
}
