<?php

use Illuminate\Database\Seeder;
use App\Model\Accounting\ChartOfAccount;
use App\Model\SettingJournal;

class SettingJournalTransferItemSeeder extends Seeder
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

        $accounts = [
            'difference stock expenses' => $this->getAccountId('FACTORY DIFFERENCE STOCK EXPENSE'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('transfer item', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'transfer item';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'transfer item')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function getAccountId($account)
    {
        foreach ($this->chartOfAccounts as $chartOfAccount) {
            if (strtolower($chartOfAccount->name) == strtolower($account)) {
                return $chartOfAccount->id;
            }
        }
    }

    private function isExists($feature, $key)
    {
        if (SettingJournal::where('feature', $feature)->where('name', $key)->first()) {
            return true;
        }

        return false;
    }
}
