<?php

use Illuminate\Database\Seeder;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;

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
            'note receivable',
            'account receivable',
            'other account receivable',
            'inventory',
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

    private function chartOfAccount()
    {
        // ASSETS
        $cash = ['kas besar', 'kas kecil', 'pos silang'];
        $cashNumber = ['10101', '10102', '10199'];

        $bank = ['bank bca', 'bank mandiri'];
        $bankNumber = ['10201', '10202'];

        $noteReceivable = ['wesel tagih'];
        $noteReceivableNumber = ['10301'];

        $accountReceivable = ['piutang usaha'];
        $accountReceivableNumber = ['10401'];

        $otherAccountReceivable = ['piutang direksi', 'piutang karyawan', 'ppn masukan'];
        $otherAccountReceivableNumber = ['10501', '10502', '10503'];

        $inventory = ['sediaan bahan baku', 'sediaan bahan pembantu', 'sediaan barang dalam proses', 'sediaan barang jadi (manufaktur)', 'sediaan dalam perjalanan', 'sediaan lain-lain'];
        $inventoryNumber = ['10601', '10602', '10603', '10604', '10605', '10699'];

        $fixedAsset = ['tanah lokasi kota surabaya', 'bangunan pabrik', 'bangunan kantor', 'mesin', 'peralatan', 'instalasi listrik', 'inventaris pabrik', 'inventaris kantor', 'kendaraan pabrik', 'kendaraan kantor', 'kendaraan penjualan'];
        $fixedAssetNumber = ['11101', '11102', '11201', '11202', '11301', '11302', '11303', '11401', '11402', '11501', '11502', '11503'];

        $fixedAssetDepreciation = ['akumulasi penyusutan bangunan pabrik', 'akumulasi penyusutan bangunan kantor', 'akumulasi penyusutan mesin', 'akumulasi penyusutan peralatan', 'akumulasi penyusutan instalasi listrik', 'akumulasi penyusutan inventaris pabrik', 'akumulasi penyusutan inventaris kantor', 'akumulasi penyusutan kendaraan pabrik', 'akumulasi penyusutan kendaraan kantor', 'akumulasi penyusutan kendaraan penjualan'];
        $fixedAssetDepreciationNumber = ['11601', '11602', '11603', '11604', '11605', '11606', '11607', '11608', '11609', '11610'];

        $otherAsset = ['aktiva dalam proses', 'aktiva tak berwujud'];
        $otherAssetNumber = ['12101', '12102'];

        $otherAssetDepreciation = ['akumulasi amortisasi aktiva tak berwujud'];
        $otherAssetDepreciationNumber = ['12103'];

        $salesDownPayment = ['uang muka penjualan'];
        $salesDownPaymentNumber = ['13101'];

        // LIABILITIES
        $currentLiability = ['wesel bayar ', 'utang usaha', 'utang bank bca', 'utang bank mandiri'];
        $currentLiabilityNumber = ['20101', '20201', '20301', '20302'];

        $otherCurrentLiability = ['utang pihak ketiga', 'utang pembelian aktiva', 'utang ppn', 'ppn keluaran', 'utang lain-lain'];
        $otherCurrentLiabilityNumber = ['20401', '20402', '20403', '20404', '20499'];

        $longTermLiability = ['gaji ymh dibayar', 'sewa ymh dibayar', 'listrik, air & telpon ymh dibayar', 'asuransi ymh dibayar', 'lain-lain ymh dibayar', 'utang bank jangka panjang'];
        $longTermLiabilityNumber = ['20501', '20502', '20503', '20504', '20505', '21001'];

        $purchaseDownPayment = ['uang muka pembelian'];
        $purchaseDownPaymentNumber = ['20601'];

        // Equity
        $ownerEquity = ['modal disetor'];
        $ownerEquityNumber = ['30101'];

        $shareholderDistribution = ['dividen'];
        $shareholderDistributionNumber = ['30102'];

        $retainedEarning = ['laba rugi', 'laba rugi s/d tahun lalu', 'laba rugi s/d bulan lalu', 'laba rugi bulan berjalan'];
        $retainedEarningNumber = ['30103', '30104', '30105', '30106'];

        // Income
        $salesIncome = ['penjualan', 'pendapatan lain', 'retur penjualan', 'potongan penjualan', 'pendapatan (beban) selisih kas'];
        $salesIncomeNumber = ['40101', '40102', '40103', '40104', '40105'];

        $otherIncome = ['pendapatan bunga', 'pendapatan selisih pembayaran', 'pendapatan selisih kurs', 'pendapatan atas penjualan aktiva', 'potongan pembelian', 'pendapatan non operasional lain-lain'];
        $otherIncomeNumber = ['41101', '41102', '41103', '41104', '41105', '41199'];

        // Expense
        $costOfSales = ['beban pokok penjualan', 'angkutan'];
        $costOfSalesNumber = ['50101', '50102'];

        $directExpense = ['gaji dan upah tenaga kerja langsung', 'gaji dan upah tenaga kerja tidak langsung', 'trucking (angkut masuk)', 'transport pabrik', 'bahan bakar pabrik', 'listrik pabrik', 'air pabrik', 'konsumsi pabrik', 'pemeliharaan bangunan pabrik', 'pemeliharaan mesin', 'pemeliharaan peralatan', 'pemeliharaan inventaris pabrik', 'pemeliharaan & surat kendaraan pabrik', 'penyusutan bangunan pabrik', 'penyusutan mesin ', 'penyusutan peralatan', 'penyusutan instalasi listrik', 'penyusutan inventaris pabrik', 'penyusutan kendaraan pabrik', 'amortisasi aktiva lain', 'biaya rumah tangga pabrik', 'sewa tanah', 'asuransi pabrik', 'lingkungan hidup', 'pph 21', 'thr pabrik', 'beban foh lain-lain', 'gaji dan tunjangan karyawan kantor', 'konsumsi dan pengobatan kantor', 'telpon, hp, dan faximile', 'suplai dan alat administrasi', 'honorarium konsultan', 'representasi dan sumbangan', 'pemeliharaan bangunan kantor', 'pemeliharaan inventaris kantor', 'pemeliharaan & surat kendaraan kantor', 'penyusutan bangunan kantor', 'penyusutan inventaris kantor', 'penyusutan kendaraan kantor', 'bahan bakar kantor', 'transport kantor', 'administrasi bank', 'listrik dan air', 'pbb', 'pos dan dokumen', 'perizinan', 'asuransi kantor', 'pph 21', 'thr kantor', 'lain-lain', 'gaji dan tunjangan karyawan penjualan', 'konsumsi dan pengobatan penjualan', 'bahan bakar penjualan', 'transport penjualan', 'iklan dan promosi', 'komisi penjualan', 'pengiriman barang', 'pemeliharaan & surat kendaraan penjualan', 'penyusutan kendaraan penjualan', 'perjalanan dinas', 'garansi', 'thr penjualan', 'lain-lain'];
        $directExpenseNumber = ['51101', '51102', '51103', '51104', '51105', '51106', '51107', '51108', '51109', '51110', '51111', '51112', '51113', '51114', '51115', '51116', '51117', '51118', '51119', '51120', '51121', '51122', '51123', '51124', '51125', '51126', '51199', '52101', '52102', '52103', '52104', '52105', '52106', '52107', '52108', '52109', '52110', '52111', '52112', '52113', '52114', '52115', '52116', '52117', '52118', '52119', '52120', '52121', '52122', '52199', '53101', '53102', '53103', '53104', '53105', '53106', '53107', '53108', '53109', '53110', '53111', '53112', '53199'];

        $otherExpense = ['beban bunga', 'beban selisih pembayaran', 'beban selisih kurs', 'beban non operasional lain-lain'];
        $otherExpenseNumber = ['54101', '54102', '54103', '54199'];

        for ($i = 0; $i < count($cash); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'cash')->first()->id;
            $chartOfAccount->number = $cashNumber[$i];
            $chartOfAccount->name = $cash[$i];
            $chartOfAccount->alias = $cash[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($bank); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'bank')->first()->id;
            $chartOfAccount->number = $bankNumber[$i];
            $chartOfAccount->name = $bank[$i];
            $chartOfAccount->alias = $bank[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($noteReceivable); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'note receivable')->first()->id;
            $chartOfAccount->number = $noteReceivableNumber[$i];
            $chartOfAccount->name = $noteReceivable[$i];
            $chartOfAccount->alias = $noteReceivable[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($inventory); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'inventory')->first()->id;
            $chartOfAccount->number = $inventoryNumber[$i];
            $chartOfAccount->name = $inventory[$i];
            $chartOfAccount->alias = $inventory[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($accountReceivable); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'account receivable')->first()->id;
            $chartOfAccount->number = $accountReceivableNumber[$i];
            $chartOfAccount->name = $accountReceivable[$i];
            $chartOfAccount->alias = $accountReceivable[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherAccountReceivable); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other account receivable')->first()->id;
            $chartOfAccount->number = $otherAccountReceivableNumber[$i];
            $chartOfAccount->name = $otherAccountReceivable[$i];
            $chartOfAccount->alias = $otherAccountReceivable[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($fixedAsset); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'fixed asset')->first()->id;
            $chartOfAccount->number = $fixedAssetNumber[$i];
            $chartOfAccount->name = $fixedAsset[$i];
            $chartOfAccount->alias = $fixedAsset[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($fixedAssetDepreciation); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'fixed asset depreciation')->first()->id;
            $chartOfAccount->number = $fixedAssetDepreciationNumber[$i];
            $chartOfAccount->name = $fixedAssetDepreciation[$i];
            $chartOfAccount->alias = $fixedAssetDepreciation[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherAsset); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other asset')->first()->id;
            $chartOfAccount->number = $otherAssetNumber[$i];
            $chartOfAccount->name = $otherAsset[$i];
            $chartOfAccount->alias = $otherAsset[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherAssetDepreciation); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other asset amortization')->first()->id;
            $chartOfAccount->number = $otherAssetDepreciationNumber[$i];
            $chartOfAccount->name = $otherAssetDepreciation[$i];
            $chartOfAccount->alias = $otherAssetDepreciation[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($currentLiability); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'current liability')->first()->id;
            $chartOfAccount->number = $currentLiabilityNumber[$i];
            $chartOfAccount->name = $currentLiability[$i];
            $chartOfAccount->alias = $currentLiability[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherCurrentLiability); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other current liability')->first()->id;
            $chartOfAccount->number = $otherCurrentLiabilityNumber[$i];
            $chartOfAccount->name = $otherCurrentLiability[$i];
            $chartOfAccount->alias = $otherCurrentLiability[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($salesDownPayment); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'sales down payment')->first()->id;
            $chartOfAccount->number = $salesDownPaymentNumber[$i];
            $chartOfAccount->name = $salesDownPayment[$i];
            $chartOfAccount->alias = $salesDownPayment[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($longTermLiability); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'long term liability')->first()->id;
            $chartOfAccount->number = $longTermLiabilityNumber[$i];
            $chartOfAccount->name = $longTermLiability[$i];
            $chartOfAccount->alias = $longTermLiability[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($purchaseDownPayment); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'purchase down payment')->first()->id;
            $chartOfAccount->number = $purchaseDownPaymentNumber[$i];
            $chartOfAccount->name = $purchaseDownPayment[$i];
            $chartOfAccount->alias = $purchaseDownPayment[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($ownerEquity); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'owner equity')->first()->id;
            $chartOfAccount->number = $ownerEquityNumber[$i];
            $chartOfAccount->name = $ownerEquity[$i];
            $chartOfAccount->alias = $ownerEquity[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($shareholderDistribution); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'shareholder distribution')->first()->id;
            $chartOfAccount->number = $shareholderDistributionNumber[$i];
            $chartOfAccount->name = $shareholderDistribution[$i];
            $chartOfAccount->alias = $shareholderDistribution[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($retainedEarning); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'retained earning')->first()->id;
            $chartOfAccount->number = $retainedEarningNumber[$i];
            $chartOfAccount->name = $retainedEarning[$i];
            $chartOfAccount->alias = $retainedEarning[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($salesIncome); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'sales income')->first()->id;
            $chartOfAccount->number = $salesIncomeNumber[$i];
            $chartOfAccount->name = $salesIncome[$i];
            $chartOfAccount->alias = $salesIncome[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherIncome); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other income')->first()->id;
            $chartOfAccount->number = $otherIncomeNumber[$i];
            $chartOfAccount->name = $otherIncome[$i];
            $chartOfAccount->alias = $otherIncome[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($costOfSales); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'cost of sales')->first()->id;
            $chartOfAccount->number = $costOfSalesNumber[$i];
            $chartOfAccount->name = $costOfSales[$i];
            $chartOfAccount->alias = $costOfSales[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($directExpense); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'direct expense')->first()->id;
            $chartOfAccount->number = $directExpenseNumber[$i];
            $chartOfAccount->name = $directExpense[$i];
            $chartOfAccount->alias = $directExpense[$i];
            $chartOfAccount->save();
        }

        for ($i = 0; $i < count($otherExpense); $i++) {
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = ChartOfAccountType::where('name', 'other expense')->first()->id;
            $chartOfAccount->number = $otherExpenseNumber[$i];
            $chartOfAccount->name = $otherExpense[$i];
            $chartOfAccount->alias = $otherExpense[$i];
            $chartOfAccount->save();
        }
    }
}
