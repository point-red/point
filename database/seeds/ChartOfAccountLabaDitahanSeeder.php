<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use Illuminate\Database\Seeder;

class ChartOfAccountLabaDitahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->chartOfAccount();
    }

    private function chartOfAccount()
    {
        $typeId = ChartOfAccountType::where('name', 'RETAINED EARNING')->first()->id;
        $chartOfAccount = new ChartOfAccount;
        $chartOfAccount->type_id = $typeId;
        $chartOfAccount->number = $typeId."001";
        $chartOfAccount->name = "RETAINED EARNING";
        $chartOfAccount->alias = "LABA DITAHAN";
        $chartOfAccount->position = "CREDIT";
        $chartOfAccount->archived_at = date("Y-m-d H:i:s");
        $chartOfAccount->save();

        $chartOfAccount = new ChartOfAccount;
        $chartOfAccount->type_id = $typeId;
        $chartOfAccount->number = $typeId."002";
        $chartOfAccount->name = "RETAINED EARNING";
        $chartOfAccount->alias = "LABA DITAHAN";
        $chartOfAccount->position = "DEBIT";
        $chartOfAccount->archived_at = date("Y-m-d H:i:s");
        $chartOfAccount->save();
    }
}
