<?php

namespace App\Console\Commands;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountSubLedger;
use App\Model\Master\PricingGroup;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AlterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            if (PricingGroup::all()->count() == 0) {
                $pricingGroup = new PricingGroup;
                $pricingGroup->label = 'DEFAULT';
                $pricingGroup->save();
            }

            $subLedger = [
                'inventory',
                'account payable',
                'purchase down payment',
                'account receivable',
                'sales down payment',
            ];

            $subLedgerAlias = [
                'sediaan',
                'utang usaha',
                'uang muka pembelian',
                'piutang usaha',
                'uang muka penjualan',
            ];

            for ($i = 0; $i < count($subLedger); $i++) {
                $chartOfAccountSubLedger = new ChartOfAccountSubLedger;
                $chartOfAccountSubLedger->name = $subLedger[$i];
                $chartOfAccountSubLedger->alias = $subLedgerAlias[$i];
                $chartOfAccountSubLedger->save();
            }

            $arrInventory = ['sediaan bahan baku', 'sediaan bahan pembantu', 'sediaan barang dalam proses', 'sediaan barang jadi (manufaktur)', 'sediaan dalam perjalanan', 'sediaan lain-lain'];
            $arrAcReceivable = ['piutang usaha', 'piutang direksi', 'piutang karyawan'];
            $arrDpSales = ['uang muka penjualan'];
            $arrAcPayable = ['utang usaha'];
            $arrDpPurchase = ['uang muka pembelian'];

            foreach ($arrInventory as $acc) {
                $account = ChartOfAccount::where('name', $acc)->first();
                if ($account) {
                    $account->sub_ledger_id = ChartOfAccountSubLedger::where('name', 'inventory')->first()->id;
                    $account->save();
                }
            }

            foreach ($arrAcReceivable as $acc) {
                $account = ChartOfAccount::where('name', $acc)->first();
                if ($account) {
                    $account->sub_ledger_id = ChartOfAccountSubLedger::where('name', 'account receivable')->first()->id;
                    $account->save();
                }
            }

            foreach ($arrDpSales as $acc) {
                $account = ChartOfAccount::where('name', $acc)->first();
                if ($account) {
                    $account->sub_ledger_id = ChartOfAccountSubLedger::where('name', 'sales down payment')->first()->id;
                    $account->save();
                }
            }

            foreach ($arrAcPayable as $acc) {
                $account = ChartOfAccount::where('name', $acc)->first();
                if ($account) {
                    $account->sub_ledger_id = ChartOfAccountSubLedger::where('name', 'account payable')->first()->id;
                    $account->save();
                }
            }

            foreach ($arrDpPurchase as $acc) {
                $account = ChartOfAccount::where('name', $acc)->first();
                if ($account) {
                    $account->sub_ledger_id = ChartOfAccountSubLedger::where('name', 'purchase down payment')->first()->id;
                    $account->save();
                }
            }
        }
    }
}
