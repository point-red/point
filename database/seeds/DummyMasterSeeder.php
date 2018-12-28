<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use Illuminate\Database\Seeder;

class DummyMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Model\Master\Customer::class, 10)->create();
        factory(\App\Model\Master\Supplier::class, 10)->create();
        factory(\App\Model\Master\Warehouse::class, 2)->create();

        $chartOfAccount = ChartOfAccount::join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName().'.id', '=', ChartOfAccount::getTableName().'.type_id')
            ->where(ChartOfAccountType::getTableName().'.name', '=', 'inventory')
            ->select(ChartOfAccount::getTableName().'.*')
            ->first();
        factory(\App\Model\Master\Item::class, 5)->create(['chart_of_account_id' => $chartOfAccount->id]);
    }
}
