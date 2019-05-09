<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use Tests\RefreshDatabase;
use App\Model\HumanResource\Kpi\KpiResult;

class KpiResultValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_result_name_should_be_unique()
    {
        $kpiResult = factory(KpiResult::class)->create();

        $data = [
            'score_min' => $kpiResult->score_min,
            'score_max' => $kpiResult->score_max,
            'criteria' => $kpiResult->criteria,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/results', $data, [$this->headers]);

        $response->assertJsonStructure([
            'errors' => ['score_min', 'score_max', 'criteria', 'notes'],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiResult->id,
            'score_min' => $kpiResult->score_min,
            'score_max' => $kpiResult->score_max,
            'criteria' => $kpiResult->criteria,
            'notes' => $kpiResult->notes,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/results/'.$kpiResult->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'errors' => ['score_min', 'score_max', 'criteria'],
        ]);

        $response->assertStatus(200);

        $data = [
            'id' => $kpiResult->id,
            'score_min' => $kpiResult->score_min,
            'score_max' => $kpiResult->score_max,
            'criteria' => $kpiResult->criteria,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/results/'.$kpiResult->id, $data, [$this->headers]);

        $response->assertJsonStructure([
            'errors' => ['notes'],
        ]);

        $response->assertStatus(422);
    }
}
