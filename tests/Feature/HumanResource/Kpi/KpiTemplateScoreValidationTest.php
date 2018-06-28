<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTemplateScoreValidationTest extends TestCase
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
        $kpiTemplateScore = factory(KpiTemplateScore::class)->create();

        $data = [];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/template-scores', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['kpi_template_indicator_id'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiTemplateScore->id,
            'kpi_template_indicator_id' => '',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/template-scores/'.$kpiTemplateScore->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['kpi_template_indicator_id'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
