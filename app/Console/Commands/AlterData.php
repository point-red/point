<?php

namespace App\Console\Commands;

use App\Model\Master\PricingGroup;
use App\Model\Plugin\PinPoint\InterestReason;
use App\Model\Plugin\PinPoint\NoInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationNoInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
use App\Model\Plugin\PinPoint\SimilarProduct;
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

            $this->line('interest reason');
            $salesVisitationInterestReasons = SalesVisitationInterestReason::groupBy('name')->get();
            foreach ($salesVisitationInterestReasons as $salesVisitationInterestReason) {
                $interestReason = InterestReason::where('name', '=', $salesVisitationInterestReason->name)->first();
                $this->line('a: ' . $interestReason->id);
                if (!$interestReason) {
                    $interestReason = new InterestReason;
                    $interestReason->name = $salesVisitationInterestReason->name;
                    $interestReason->created_by = $salesVisitationInterestReason->salesVisitation->form->created_by;
                    $interestReason->updated_by = $salesVisitationInterestReason->salesVisitation->form->updated_by;
                    $interestReason->save();
                }
            }

            $this->line('no interest reason');
            $salesVisitationNoInterestReasons = SalesVisitationNoInterestReason::groupBy('name')->get();
            foreach ($salesVisitationNoInterestReasons as $salesVisitationNoInterestReason) {
                $noInterestReason = NoInterestReason::where('name', '=', $salesVisitationNoInterestReason->name)->first();
                $this->line('b: ' . $noInterestReason->id);
                if (!$noInterestReason) {
                    $noInterestReason = new NoInterestReason;
                    $noInterestReason->name = $salesVisitationNoInterestReason->name;
                    $noInterestReason->created_by = $salesVisitationNoInterestReason->salesVisitation->form->created_by;
                    $noInterestReason->updated_by = $salesVisitationNoInterestReason->salesVisitation->form->updated_by;
                    $noInterestReason->save();
                }
            }

            $this->line('similar product');
            $salesVisitationSimilarProducts = SalesVisitationSimilarProduct::groupBy('name')->get();
            foreach ($salesVisitationSimilarProducts as $salesVisitationSimilarProduct) {
                $similarProduct = SimilarProduct::where('name', '=', $salesVisitationSimilarProduct->name)->first();
                $this->line('c: ' . $similarProduct->id);
                if (!$similarProduct) {
                    $similarProduct = new SimilarProduct;
                    $similarProduct->name = $salesVisitationSimilarProduct->name;
                    $similarProduct->created_by = $salesVisitationSimilarProduct->salesVisitation->form->created_by;
                    $similarProduct->updated_by = $salesVisitationSimilarProduct->salesVisitation->form->updated_by;
                    $similarProduct->save();
                }
            }
        }
    }
}
