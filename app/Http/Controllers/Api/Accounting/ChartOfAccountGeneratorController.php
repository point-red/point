<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ChartOfAccountGeneratorController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        Excel::import(new ChartOfAccountImport(), storage_path('app/template/chart_of_accounts_manufacture.xlsx'));

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);

        $accounts = ChartOfAccount::all();
        foreach ($accounts as $account) {
            if (!CutOffAccount::where('chart_of_account_id', $account->id)
                ->where('cut_off_id', CutOff::where('id', '>', 0)->first()->id)
                ->first()) {
                $cutOffAccount = new CutOffAccount;
                $cutOffAccount->chart_of_account_id = $account->id;
                $cutOffAccount->cut_off_id = CutOff::where('id', '>', 0)->first()->id;
                if ($account->type->is_debit == true) {
                    $cutOffAccount->debit = 0;
                } else {
                    $cutOffAccount->credit = 0;
                }

                $cutOffAccount->save();
            }
        }

        DB::connection('tenant')->commit();

        return response()->json([
            'status' => 'success',
            'message' => 'generate account succeed'
        ], 200);
    }
}
