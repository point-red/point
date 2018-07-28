<?php

namespace App\Helpers\Ratio;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use DateTime;

class Ratio
{
    public $currentAssets = ['cash', 'bank', 'cheque', 'inventory', 'account receivable', 'other account receivable'];
    public $otherAssets = ['fixed asset', 'other asset'];
    public $cashEquivalent = ['cash', 'bank', 'cheque'];
    public $accountReceivable = ['account receivable', 'other account receivable'];
    public $currentLiability = ['current liability', 'other current liability'];
    public $equity = ['equity'];
    public $salesIncome = ['sales income'];
    public $costOfSales = ['cost of sales'];
    public $directExpense = ['direct expense'];
    public $otherIncome = ['other income'];
    public $otherExpense = ['other expense'];

    private $rateOfReturnNetWorthDescription = 'rasio untuk mengukur kemampuan modal sendiri (tanpa laba ditahan) dalam menghasilkan pendapatan (semakin tinggi semakin baik)';
    private $totalDebtToAssetDescription = 'rasio untuk mengukur kemampuan perusahaan dalam membayar hutang-hutangnya dengan asset yang dimilikinya';
    private $totalDebtToEquityDescription = 'rasio untuk mengukur seberapa besar hutang perusahaan dibandingkan dengan modal';
    private $totalAssetTurnOverDescription = 'rasio untuk mengukur tingkat perputaran total aktiva terhadap penjualan';
    private $workingCapitalDescription = 'rasio untuk mengukur tingkat perputaran modal kerja bersih (Aktiva Lancar-Hutang Lancar) terhadap penjualan';
    private $fixedAssetTurnOverDescription = 'rasio ini berguna untuk mengevaluasi seberapa besar tingkat kemampuan perusahaan dalam memanfaatkan aktivatetap yang dimiliki secara efisien dalam rangka meningkatkan pendapatan';
    private $inventoryTurnOverDescription = 'rasio untuk mengukur tingkat efisiensi pengelolaan perputaran persediaan yang dimiliki terhadap penjualan. Semakin tinggi rasio ini akan semakin baik dan menunjukkan pengelolaan persediaan yang efisien.';
    private $averageCollectionPeriodRatioDescription = 'rasio untuk mengukur  berapa lama waktu yang dibutuhkan oleh perusahaan dalam menerima pelunasan dari konsumen.';

    public function getTotalSalesProfit($date) {
        return $this->getTotal($this->salesIncome, $date) - $this->getTotal($this->costOfSales, $date);
    }

    public function getTotalGrossProfit($date) {
        return $this->getTotalSalesProfit($date) - $this->getTotal($this->directExpense, $date);
    }

    public function getTotalNetProfit($date) {
        return $this->getTotalGrossProfit($date) + $this->getTotal($this->otherIncome, $date) - $this->getTotal($this->otherExpense, $date);
    }

    public function getRatio($a, $b) {
        if ($a == 0 || $b == 0) {
            return 0;
        }

        return $a / $b;
    }

    public function getLabel($date) {
        return date('M Y', strtotime($date));
    }

    public function addOneMonth($date) {
        return date('Y-m-d', strtotime($date . ' +1 Months'));
    }

    public function getTotalMonth($dateFrom, $dateTo) {
        $dateTimeFrom = new Datetime($dateFrom);
        $dateTimeTo = new DateTime($dateTo);
        return $dateTimeFrom->diff($dateTimeTo)->m + 1;
    }

    public function getTotal($accountType, $date) {
        $date = date('Y-m-d 23:59:59', strtotime($date));

        $chartOfAccount = ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->whereIn('chart_of_account_types.name', $accountType)->first();

        $ids = ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->whereIn('chart_of_account_types.name', $accountType)
            ->select('chart_of_accounts.*')
            ->pluck('id');

        if ($chartOfAccount->is_debit) {
            $total = Journal::whereIn('chart_of_account_id', $ids)
                ->where('date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(debit) - sum(credit) as total')
                ->pluck('total');
        } else {
            $total = Journal::whereIn('chart_of_account_id', $ids)
                ->where('date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(credit) - sum(debit) as total')
                ->pluck('total');
        }

        return $total[0];
    }
}
