<?php

namespace App\Console\Commands;

use App\Model\Account\Invoice;
use App\Model\Account\InvoiceItem;
use App\Model\Accounting\CutOffInventory;
use App\Model\Master\ItemUnit;
use App\Model\Master\PricingGroup;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InvoiceGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:generate-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices';

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
//            $invoice = new Invoice;
//            $invoice->date = date('Y-m-d');
//            $invoice->due_date = date('Y-m-14');
//            $invoice->user_id = '';
//            $invoice->project_id = '';
//            $invoice->project_name = '';
//            $invoice->project_address = '';
//            $invoice->project_email = '';
//            $invoice->project_phone = '';
//            $invoice->name = '';
//            $invoice->address = '';
//            $invoice->email = '';
//            $invoice->phone = '';
//            $invoice->sub_total = '';
//            $invoice->discount_percent = '';
//            $invoice->discount_value = '';
//            $invoice->vat = '';
//            $invoice->total = '';
//            $invoice->paidable_id = '';
//            $invoice->paidable_type = '';
//            $invoice->save();
//
//            $invoiceItem = new InvoiceItem;
//            $invoiceItem->invoice_id = '';
//            $invoiceItem->description = '';
//            $invoiceItem->quantity = '';
//            $invoiceItem->amount = '';
//            $invoiceItem->discount_percent = '';
//            $invoiceItem->discount_value = '';
//            $invoiceItem->save();
        }
    }
}
