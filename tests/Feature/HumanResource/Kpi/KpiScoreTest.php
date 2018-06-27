<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;

class KpiScoreTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi_score()
    {
        $data = [
            'kpi_template_indicator_id' => factory(KpiTemplateIndicator::class)->create()->id,
            'description' => ['description', 'description', 'description', 'description', 'description'],
            'score' => [1, 2, 3, 4, 5],
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/scores', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_scores', ['kpi_template_indicator_id' => $data['kpi_template_indicator_id']]);
    }

    /** @test */
    public function an_user_can_read_single_kpi_score()
    {
        $kpiScore = factory(KpiScore::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/scores/'.$kpiScore->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'kpi_template_indicator_id' => $kpiScore->kpi_template_indicator_id,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_score()
    {
        $kpiScores = factory(KpiScore::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/scores', [], [$this->headers]);

        foreach ($kpiScores as $kpiScore) {
            $this->assertDatabaseHas('kpi_scores', [
                'kpi_template_indicator_id' => $kpiScore->kpi_template_indicator_id,
            ]);

            $this->assertDatabaseHas('kpi_scores', [
                'kpi_template_indicator_id' => $kpiScore->kpi_template_indicator_id,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_score()
    {
        $kpiScore = factory(KpiScore::class)->create();

        $kpiScore->details()->save(factory(KpiTemplateScore::class)->create(['kpi_score_id' => $kpiScore->id]));
        $kpiScore->details()->save(factory(KpiTemplateScore::class)->create(['kpi_score_id' => $kpiScore->id]));
        $kpiScore->details()->save(factory(KpiTemplateScore::class)->create(['kpi_score_id' => $kpiScore->id]));
        $kpiScore->details()->save(factory(KpiTemplateScore::class)->create(['kpi_score_id' => $kpiScore->id]));
        $kpiScore->details()->save(factory(KpiTemplateScore::class)->create(['kpi_score_id' => $kpiScore->id]));

        $data = [
            'id' => $kpiScore->id,
            'kpi_template_indicator_id' => factory(KpiTemplateIndicator::class)->create()->id,
            'kpi_score_detail_id' => [1, 2, 3, 5],
            'description' => ['description', 'description', 'description', 'description'],
            'score' => [1, 2, 3, 5],
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/scores/'.$kpiScore->id, $data, [$this->headers]);

        $response->assertJson(['data' => ['kpi_template_indicator_id' => $data['kpi_template_indicator_id']]]);

        $this->assertDatabaseHas('kpi_scores', ['kpi_template_indicator_id' => $data['kpi_template_indicator_id']]);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_score()
    {
        $kpiScore = factory(KpiScore::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/scores/'.$kpiScore->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_scores', [
            'id' => $kpiScore->id,
        ]);
    }
}
