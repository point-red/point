<?php

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Master\ItemUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DummyMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('tenant')->beginTransaction();

        factory(\App\Model\Master\Customer::class, 10)->create();
        factory(\App\Model\Master\Supplier::class, 10)->create();
        factory(\App\Model\Master\Warehouse::class, 2)->create();

        $this->importChartOfAccount();
        $this->seedItems();

        DB::connection('tenant')->commit();
    }

    private function importChartOfAccount()
    {
        Excel::import(new ChartOfAccountImport(), storage_path('template/chart_of_accounts_manufacture.xlsx'));

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);
    }

    private function seedItems()
    {
        if (ChartOfAccount::first()) {
            $chartOfAccount = ChartOfAccount::join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName().'.id', '=', ChartOfAccount::getTableName().'.type_id')
                ->where(ChartOfAccountType::getTableName().'.name', '=', 'inventory')
                ->select(ChartOfAccount::getTableName().'.*')
                ->first();
        }

        factory(\App\Model\Master\Item::class, 5)
            ->create(['chart_of_account_id' => $chartOfAccount->id])
            ->each(function($item) {
                $unit = factory(ItemUnit::class, 1)->make()->first();
                $item->units()->save($unit);
            });
    }
}
