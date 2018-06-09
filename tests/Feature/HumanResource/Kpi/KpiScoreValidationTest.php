<?php

namespace Tests\Feature\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiScore;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiScoreValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_score_name_should_be_unique()
    {
        $kpiScore = factory(KpiScore::class)->create();

        $data = [];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/scores', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['kpi_template_indicator_id'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiScore->id,
            'kpi_template_indicator_id' => '',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/scores/'.$kpiScore->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['kpi_template_indicator_id'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
