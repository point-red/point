<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Support\Facades\DB;

class TransferKpiScoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:transfer:kpi:score';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer KPI Score Template to KPI Score Table';

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
     * @return mixedf
     */
    public function handle()
    {
        $this->line('transfer kpi template score to kpi score table');

        $projects = Project::all();

        foreach ($projects as $project) {
            $databaseName = 'point_'.strtolower($project->code);
            $this->line('Transfer KPI : ' . $project->code);

            // Update tenant database name in configuration
            config()->set('database.connections.tenant.database', strtolower($databaseName));
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();

            $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
                ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
                ->select('kpis.*')
                ->orderBy('kpis.date', 'asc')->get();

            foreach ($kpis as $key => $kpi) {

                foreach ($kpi->groups as $kpiGroup) {

                    foreach ($kpiGroup->indicators as $kpiIndicator) {

                        if (count($kpiIndicator->scores) == 0) {
                            $kpi_template_scores = KpiTemplateScore::join('kpi_template_indicators', 'kpi_template_indicators.id', '=', 'kpi_template_scores.kpi_template_indicator_id')
                                ->join('kpi_template_groups', 'kpi_template_groups.id', '=', 'kpi_template_indicators.kpi_template_group_id')
                                ->join('kpi_templates', 'kpi_templates.id', '=', 'kpi_template_groups.kpi_template_id')
                                ->select('kpi_template_scores.*')
                                ->where('kpi_template_indicators.name', $kpiIndicator['name'])
                                ->where('kpi_template_groups.name', $kpiGroup['name'])
                                ->where('kpi_templates.name', $kpi['name'])
                                ->orderBy('kpi_template_scores.score', 'asc')
                                ->get();

                            if (count($kpi_template_scores) == 0) {
                                $kpiScore = new KpiScore();
                                $kpiScore->kpi_indicator_id = $kpiIndicator['id'];
                                $kpiScore->description = $kpiIndicator['score_description'];
                                $kpiScore->score = $kpiIndicator['score'];
                                $kpiScore->save();
                            }
                            else {
                                foreach ($kpi_template_scores as $kpi_template_score) {
                                    $kpiScore = new KpiScore();
                                    $kpiScore->kpi_indicator_id = $kpiIndicator['id'];
                                    $kpiScore->description = $kpi_template_score['description'];
                                    $kpiScore->score = $kpi_template_score['score'];
                                    $kpiScore->save();
                                }
                            }
                        }
                    }
                }
            }

            DB::connection('tenant')->commit();
        }
    }
}
