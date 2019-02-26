<?php

namespace App\Helpers\Ratio;

use App\Model\Form;
use DateTime;
use App\Model\Accounting\Journal;
use App\Model\Accounting\ChartOfAccount;

class Ratio
{
    public $currentAssets = ['cash', 'bank', 'cheque', 'inventory', 'account receivable', 'other account receivable'];
    public $otherAssets = ['fixed asset', 'other asset', 'fixed asset depreciation', 'other asset amortization'];
    public $assets = ['cash', 'bank', 'cheque', 'inventory', 'account receivable', 'other account receivable', 'fixed asset', 'other asset'];
    public $cashEquivalent = ['cash', 'bank', 'cheque'];
    public $accountReceivable = ['account receivable', 'other account receivable'];
    public $liability = ['current liability', 'other current liability', 'long term liability'];
    public $currentLiability = ['current liability', 'other current liability'];
    public $equity = ['owner equity', 'shareholder distribution', 'retained earning'];
    public $salesIncome = ['sales income'];
    public $costOfSales = ['cost of sales'];
    public $directExpense = ['direct expense'];
    public $otherIncome = ['other income'];
    public $otherExpense = ['other expense'];

    public function getTotalSalesProfit($date)
    {
        return $this->getTotal($this->salesIncome, $date) - $this->getTotal($this->costOfSales, $date);
    }

    public function getTotalGrossProfit($date)
    {
        return $this->getTotalSalesProfit($date) - $this->getTotal($this->directExpense, $date);
    }

    public function getTotalNetProfit($date)
    {
        return $this->getTotalGrossProfit($date) + $this->getTotal($this->otherIncome, $date) - $this->getTotal($this->otherExpense, $date);
    }

    public function getTotalNetWorkingCapital($date)
    {
        return $this->getTotal($this->currentAssets, $date) - $this->getTotal($this->currentLiability, $date) - $this->getTotal(['note payable'], $date);
    }

    public function getRatio($a, $b)
    {
        if ($a == 0 || $b == 0) {
            return 0;
        }

        return $a / $b;
    }

    public function getLabel($date)
    {
        return date('M Y', strtotime($date));
    }

    public function addOneMonth($date)
    {
        return date('Y-m-d', strtotime($date.' +1 Months'));
    }

    public function getTotalMonth($dateFrom, $dateTo)
    {
        $dateTimeFrom = new Datetime($dateFrom);
        $dateTimeTo = new DateTime($dateTo);

        return $dateTimeFrom->diff($dateTimeTo)->m + 1;
    }

    public function getTotal($accountType, $date)
    {
        $date = date('Y-m-d 23:59:59', strtotime($date));

        $chartOfAccount = ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->whereIn('chart_of_account_types.name', $accountType)->first();

        $ids = ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->whereIn('chart_of_account_types.name', $accountType)
            ->select('chart_of_accounts.*')
            ->pluck('id');

        if (! $chartOfAccount) {
            return 0;
        }

        if ($chartOfAccount->is_debit) {
            $total = Journal::join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
                ->whereIn('chart_of_account_id', $ids)
                ->where('forms.date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(debit) - sum(credit) as total')
                ->pluck('total');
        } else {
            $total = Journal::join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
                ->whereIn('chart_of_account_id', $ids)
                ->where('forms.date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(credit) - sum(debit) as total')
                ->pluck('total');
        }

        return $total[0];
    }
}
