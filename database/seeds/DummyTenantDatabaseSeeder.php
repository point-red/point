<?php

use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiCategory;
use App\Model\HumanResource\Kpi\KpiGroup;
use App\Model\HumanResource\Kpi\KpiResult;
use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\HumanResource\Kpi\KpiScoreDetail;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use App\Model\Master\Person;
use App\Model\Master\Warehouse;
use Illuminate\Database\Seeder;

class DummyTenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Master
        factory(Warehouse::class, 2)->create();
        factory(Person::class, 2)->create();

        // Kpi
        factory(KpiTemplateIndicator::class, 2)->create();
        factory(KpiResult::class, 1)->create();
        factory(Kpi::class, 2)->create();
        factory(KpiScore::class, 2)->create()
            ->each(function ($kpiScore) {
                $kpiScore->details()->save(factory(KpiScoreDetail::class)->create(['kpi_score_id' => $kpiScore->id]));
                $kpiScore->details()->save(factory(KpiScoreDetail::class)->create(['kpi_score_id' => $kpiScore->id]));
                $kpiScore->details()->save(factory(KpiScoreDetail::class)->create(['kpi_score_id' => $kpiScore->id]));
                $kpiScore->details()->save(factory(KpiScoreDetail::class)->create(['kpi_score_id' => $kpiScore->id]));
                $kpiScore->details()->save(factory(KpiScoreDetail::class)->create(['kpi_score_id' => $kpiScore->id]));
            });
    }
}
