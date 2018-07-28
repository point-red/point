<?php

namespace App\Helpers;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use DateTime;

class RatioHelper
{
    private $currentAssets = ['cash', 'bank', 'cheque', 'inventory', 'account receivable', 'other account receivable'];
    private $otherAssets = ['fixed asset', 'other asset'];
    private $cashEquivalent = ['cash', 'bank', 'cheque'];
    private $accountReceivable = ['account receivable', 'other account receivable'];
    private $currentLiability = ['current liability', 'other current liability'];
    private $sales = ['sales income'];
    private $grossProfit = ['current liability', 'other current liability'];
    private $netProfit = ['current liability', 'other current liability'];

    private $currentRatioDescription = 'rasio untuk mengukur kemampuan perusahaan dalam membayar kewajiban finansial jangka pendek dengan mengunakan asset lancar, nilai ideal adalah 150%';
    private $cashRatioDescription = 'rasio untuk mengukur kemampuan perusahaan dalam membayar kewajiban finansial jangka pendek dengan mengunakan kas dan setara kas yang tersedia, nilai ideal adalah 150%';
    private $acidTestRatioDescription = 'rasio untuk mengukur kemampuan perusahaan dalam membayar kewajiban finansial jangka pendek dengan mengunakan asset lancar yang lebih likuid (Liquid Assets), nilai ideal adalah 150%';
    private $grossProfitMarginDescription = 'rasio untuk mengukur kemampuan perusahaan dalam mendapatkan laba kotor dari penjualan. (semakin tinggi semakin baik)';
    private $netProfitMarginDescription = 'rasio untuk mengukur kemampuan perusahaan dalam mendapatkan laba bersih dari penjualan (semakin tinggi semakin baik)';
    private $rateOfReturnInvestmentDescription = 'rasio untuk mengukur kemampuan asset untuk menghasilkan pendapatan bersih (semakin tinggi semakin baik)';
    private $returnOnEquityDescription = 'rasio untuk mengukur kemampuan modal dan laba ditahan untuk menghasilkan pendapatan bersih (semakin tinggi semakin baik)';
    private $rateOfReturnNetWorthDescription = 'rasio untuk mengukur kemampuan modal sendiri (tanpa laba ditahan) dalam menghasilkan pendapatan (semakin tinggi semakin baik)';
    private $totalDebtToAssetDescription = 'rasio untuk mengukur kemampuan perusahaan dalam membayar hutang-hutangnya dengan asset yang dimilikinya';
    private $totalDebtToEquityDescription = 'rasio untuk mengukur seberapa besar hutang perusahaan dibandingkan dengan modal';
    private $totalAssetTurnOverDescription = 'rasio untuk mengukur tingkat perputaran total aktiva terhadap penjualan';
    private $workingCapitalDescription = 'rasio untuk mengukur tingkat perputaran modal kerja bersih (Aktiva Lancar-Hutang Lancar) terhadap penjualan';
    private $fixedAssetTurnOverDescription = 'rasio ini berguna untuk mengevaluasi seberapa besar tingkat kemampuan perusahaan dalam memanfaatkan aktivatetap yang dimiliki secara efisien dalam rangka meningkatkan pendapatan';
    private $inventoryTurnOverDescription = 'rasio untuk mengukur tingkat efisiensi pengelolaan perputaran persediaan yang dimiliki terhadap penjualan. Semakin tinggi rasio ini akan semakin baik dan menunjukkan pengelolaan persediaan yang efisien.';
    private $averageCollectionPeriodRatioDescription = 'rasio untuk mengukur  berapa lama waktu yang dibutuhkan oleh perusahaan dalam menerima pelunasan dari konsumen.';

    /**
     * Current Asset
     * CURRENT ASSET / CURRENT LIABILITY
     *
     * @param $date
     *
     * @return float|int
     */
    public function getCurrentAsset($date) {
        return $this->calculate($this->getTotal($this->currentAssets, $date), $this->getTotal($this->currentLiability, $date));
    }

    private function getRatio($dateFrom, $dateTo) {
        $date = date('Y-m-d 23:59:59', strtotime($dateFrom));
        $dateTimeFrom = new Datetime($dateFrom);
        $dateTimeTo = new DateTime($dateTo);
        $months = $dateTimeFrom->diff($dateTimeTo)->m + 1;

        $values = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, date('M Y', strtotime($date)));
            array_push($values, $this->calculate(1, 1));

            $date = date('Y-m-d', strtotime($date . ' +1 Months'));
        }

        return response()->json([
            'data' => [
                'description' => '',
                'result' => '',
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    private function calculate($a, $b) {
        if ($a == 0 || $b == 0) {
            return 0;
        }

        return $a / $b;
    }

    private function getTotal($accountType, $date) {
        $ids = ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->whereIn('chart_of_account_types.name', $accountType)
            ->select('chart_of_accounts.*')
            ->pluck('id');

        $total = Journal::whereIn('chart_of_account_id', $ids)
            ->where('date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
            ->selectRaw('sum(credit) - sum(debit) as total')
            ->pluck('total');

        return $total;
    }
}
