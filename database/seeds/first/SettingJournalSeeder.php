<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
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
        $this->expedition();
        $this->stockCorrection();
        $this->transferItem();
        $this->manufacture();
        $this->inventoryUsage();
        $this->inventoryAudit();
        $this->cutoff();
    }

    private function openingBalanceInventory()
    {
        $accounts = [
            'capital' => $this->getAccountId('capital'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('opening balance inventory', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'opening balance inventory';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'opening balance inventory')->where('name', $key)->first();
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
            if (! $this->isExists('purchase', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'purchase';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'purchase')->where('name', $key)->first();
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
            if (! $this->isExists('sales', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'sales';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'sales')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function expedition()
    {
        $accounts = [
            'account payable expedition' => $this->getAccountId('account payable expedition'),
            'expedition down payment' => $this->getAccountId('expedition down payment'),
            'income tax receivable' => $this->getAccountId('income tax receivable'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('expedition', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'expedition';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'expedition')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function manufacture()
    {
        $accounts = [
            'work in process inventory' => $this->getAccountId('work in process inventory'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('manufacture', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'manufacture';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'manufacture')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function transferItem()
    {
        $accounts = [
            'inventory in distribution' => $this->getAccountId('inventory in distribution'),
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

    private function stockCorrection()
    {
        $accounts = [
            'difference stock expense' => $this->getAccountId('difference stock expense'),
            'other income' => $this->getAccountId('other income'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('stock correction', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'stock correction';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'stock correction')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function inventoryUsage()
    {
        $accounts = [
            'difference stock expense' => $this->getAccountId('difference stock expense'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('inventory usage', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'inventory usage';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'inventory usage')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function inventoryAudit()
    {
        $accounts = [
            'difference stock expense' => $this->getAccountId('difference stock expense'),
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('inventory audit', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'inventory audit';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'inventory audit')->where('name', $key)->first();
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            }
        }
    }

    private function cutoff()
    {
        $retainedEarning = $this->getAccountId('RETAINED EARNING');
        if (!$retainedEarning){
            $typeId = ChartOfAccountType::where('name', 'NET INCOME')->first()->id;
            
            $labaDitahan = new ChartOfAccount;
            $labaDitahan->type_id = $typeId;
            $labaDitahan->number = "32000";
            $labaDitahan->name = "RETAINED EARNING";
            $labaDitahan->alias = 'LABA DITAHAN';
            $labaDitahan->position = "CREDIT";
            $labaDitahan->is_locked = true;
            $labaDitahan->save();
            $retainedEarning = $labaDitahan->id;
        }
        $accounts = [
            'retained earning' => $retainedEarning,
        ];

        foreach ($accounts as $key => $value) {
            if (! $this->isExists('cutoff', $key)) {
                $settingJournal = new SettingJournal;
                $settingJournal->feature = 'cutoff';
                $settingJournal->name = $key;
                $settingJournal->description = '';
                $settingJournal->chart_of_account_id = $value;
                $settingJournal->save();
            } else {
                $settingJournal = SettingJournal::where('feature', 'cutoff')->where('name', $key)->first();
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
