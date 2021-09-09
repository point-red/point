<?php

namespace App\Console\Commands;

use App\Imports\Template\ItemImport;
use App\Model\Project\Project;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoiceItem;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AlterItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-item';

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
        $projects = Project::where('is_generated', true)->where('code', 'kopibara')->get();
        foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);

            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'point_'.strtolower($project->code));

            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();

            $invoiceItems = PurchaseInvoiceItem::all();

            foreach ($invoiceItems as $invoiceItem) {
                $count = PurchaseInvoiceItem::where('production_number', $invoiceItem->production_number)
                ->where("purchase_invoice_id", $invoiceItem->purchase_invoice_id)
                ->where("item_id", $invoiceItem->item_id)
                ->count();
                if($count > 1) {
                    $count2 = PurchaseReceiveItem::where('purchase_receive_id', $invoiceItem->purchase_receive_id)->count();
                    $count3 = PurchaseInvoiceItem::where("purchase_invoice_id", $invoiceItem->purchase_invoice_id)->count();
                        if($count2 != $count3) {
                            $this->line("invoice " . $invoiceItem->purchaseInvoice->id . " = " . $count . " = " . $count2 . " = " . $count3);
                        }
                }
            }


            DB::connection('tenant')->commit();
        }
    }
}
