<?php

namespace App\Console\Commands;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
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
            if ($project->group != 'kopibara') {
                continue;
            }

            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $items = Item::all();

            $kopibaraCodes = [
                'B001',
                'B005',
                'B008',
                'B011',
                'R001',
                'R002',
                'R003',
                'R008',
                'R012',
                'Y007',
                'P012',
                'P013',
                'P014',
            ];

            $kopibaraItems = [
                'REGULER BUBUK 250GR',
                'REGULER KOPI GULA 20GR',
                'REGULER CUP HOREKA 1KG',
                'REGULER 3 IN 1 BULK 1KG',
                'PREMIUM PACK 70GR',
                'PREMIUM PACK 5GR',
                'PREMIUM CUP KOPI GULA',
                'PREMIUM BIJI SEAL PACK 1KG',
                'PREMIUM PACK HOREKA 1KG',
                'NEW GEN BULK PACK 1KG',
                'KOPI BARA ARABICA FLORES BAJAWA (ARFBJ)',
                'KOPI BARA ARABICA KINTAMANI (AKTB)',
                'KOPI BARA ARABICA TORAJA (ATJ)',
            ];

            foreach (Item::all() as $dbItem) {
                foreach ($kopibaraItems as $kopibaraItem) {
                    if (substr($dbItem->name, 5) == $kopibaraItem) {
                        $dbItem->code = substr($dbItem->name, 0, 4);
                        $dbItem->name = $kopibaraItem;
                        $dbItem->save();
                        break;
                    }
                }
            }

            foreach ($kopibaraItems as $index => $kopibaraItem) {
                $dbItem = Item::where('name', $kopibaraItem)->first();
                if (! $dbItem) {
                    $item = new Item;
                    $item->code = $kopibaraCodes[$index];
                    $item->name = $kopibaraItem;
                    $account = ChartOfAccount::where('name', 'sediaan barang jadi (manufaktur)')->first();
                    $item->chart_of_account_id = $account->id;
                    $item->save();

                    $itemUnit = new ItemUnit;
                    $itemUnit->name = 'pcs';
                    $itemUnit->label = 'pcs';
                    $itemUnit->item_id = $item->id;
                    $itemUnit->save();
                }
            }

            $account = ChartOfAccount::where('name', 'sediaan barang jadi (manufaktur)')->first();
            foreach ($items as $item) {
                // Add account into items that doesn't have account
                if ($item->chart_of_account_id == null) {
                    $item->chart_of_account_id = $account->id;
                    $item->save();
                }

                // Add unit into items that doesn't have unit
                if ($item->units->count() == 0) {
                    $itemUnit = new ItemUnit;
                    $itemUnit->name = 'pcs';
                    $itemUnit->label = 'pcs';
                    $itemUnit->item_id = $item->id;
                    $itemUnit->save();
                }
            }
        }
    }
}
