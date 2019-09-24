<?php

namespace App\Console\Commands;

use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Model\Accounting\ChartOfAccount;

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

            $kopibaraItems = [
                'B001 REGULER BUBUK 250GR',
                'B005 REGULER KOPI GULA 20GR',
                'B008 REGULER CUP HOREKA 1KG',
                'B011 REGULER 3 IN 1 BULK 1KG',
                'R001 PREMIUM PACK 70GR',
                'R002 PREMIUM PACK 5GR',
                'R003 PREMIUM CUP KOPI GULA',
                'R008 PREMIUM BIJI SEAL PACK 1KG',
                'R012 PREMIUM PACK HOREKA 1KG',
                'Y007 NEW GEN BULK PACK 1KG'
            ];

            foreach ($kopibaraItems as $kopibaraItem) {
                if (!Item::where('name', $kopibaraItem)->first()) {
                    $item = new Item;
                    $item->name = $kopibaraItem;
                    $item->save();
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
