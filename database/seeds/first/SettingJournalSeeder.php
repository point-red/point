<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\SettingJournal;
use Illuminate\Database\Seeder;

class SettingJournalSeeder extends Seeder
{
    public $chartOfAccounts;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->chartOfAccounts = ChartOfAccount::all();

        $this->openingBalanceInventory();
        $this->purchase();
        $this->sales();
    }

    private function openingBalanceInventory()
    {
        $accounts = [
            'retained earning' => $this->getAccountId('retained earning'),
        ];

        foreach ($accounts as $key => $value) {
            if (!$this->isExists('opening balance inventory', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'opening balance inventory';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function purchase()
    {
        $accounts = [
            'account payable' => $this->getAccountId('account payable'),
            'down payment' => $this->getAccountId('purchase down payment'),
            'income tax receivable' => $this->getAccountId('income tax receivable'),
        ];

        foreach ($accounts as $key => $value) {
            if (!$this->isExists('purchase', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'purchase';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function sales()
    {
        $accounts = [
            'account receivable' => $this->getAccountId('account receivable'),
            'down payment' => $this->getAccountId('sales down payment'),
            'discount' => $this->getAccountId('sales discount'),
            'income tax payable' => $this->getAccountId('income tax payable'),
            'sales income' => $this->getAccountId('sales'),
            'cost of sales' => $this->getAccountId('cost of sales'),
        ];

        foreach ($accounts as $key => $value) {
            if (!$this->isExists('sales', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'sales';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function getAccountId($account)
    {
        foreach ($this->chartOfAccounts as $chartOfAccount) {
            if ($chartOfAccount->name == $account) {
                return $chartOfAccount->id;
            }
        }
    }

    private function isExists ($feature, $key) {
        if (SettingJournal::where('feature', $feature)->where('name', $key)->first()) {
            return true;
        }

        return false;
    }
}
