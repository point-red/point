<?php

use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyKpiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('tenant')->beginTransaction();

        $kpiTemplate = new KpiTemplate;
        $kpiTemplate->name = 'PENGAWAS / SUPERVISOR PRODUKSI';
        $kpiTemplate->save();

        $groupName = ['FUNGSI MANAJERIAL', 'FUNGSI TEKNIS', 'LAIN LAIN'];
        for ($groupIndex = 0; $groupIndex < count($groupName); $groupIndex++) {
            $kpiTemplateGroup = new KpiTemplateGroup;
            $kpiTemplateGroup->kpi_template_id = $kpiTemplate->id;
            $kpiTemplateGroup->name = $groupName[$groupIndex];
            $kpiTemplateGroup->save();

            $indicatorName = [
                'Tercapainya Ketertiban dan kedisiplinan bawahan',
                'Terlaksananya proses produksi sesuai standar operasi yang ditentukan perusahaan',
                'Terlaksananya briefing rutin kepada bawahan',
                'Terlaksananya laporan kerja secara priodik',
                'Terlaksananya analisa permasalahan serta solusinya'
            ];

            for ($indicatorIndex = 0; $indicatorIndex < count($indicatorName); $indicatorIndex++) {
                $kpiTemplateIndicator = new KpiTemplateIndicator;
                $kpiTemplateIndicator->kpi_template_group_id = $kpiTemplateGroup->id;
                $kpiTemplateIndicator->name = $indicatorName[$indicatorIndex];
                $kpiTemplateIndicator->weight = 8;
                $kpiTemplateIndicator->target = 5;
                $kpiTemplateIndicator->save();

                $scores = [
                    ['score' => 5, 'description' => 'selalu mencapai target produksi'],
                    ['score' => 4, 'description' => 'sering mencapai target produksi'],
                    ['score' => 3, 'description' => 'terkadang mencapai target produksi'],
                    ['score' => 2, 'description' => 'sering tidak mencapai target produksi'],
                    ['score' => 1, 'description' => 'selalu tidak mencapai target produksi']
                ];

                info($scores);

                for ($scoreIndex = 0; $scoreIndex < count($scores); $scoreIndex++) {
                    $kpiTemplateScore = new KpiTemplateScore;
                    $kpiTemplateScore->kpi_template_indicator_id = $kpiTemplateIndicator->id;
                    $kpiTemplateScore->score = $scores[$scoreIndex]['score'];
                    $kpiTemplateScore->description = $scores[$scoreIndex]['description'];
                    $kpiTemplateScore->save();
                }
            }
        }

        DB::connection('tenant')->commit();
    }
}
