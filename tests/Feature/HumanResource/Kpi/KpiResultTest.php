<?php

namespace Tests\Feature\Master;

use App\Model\HumanResource\Kpi\KpiResult;
use Tests\RefreshDatabase;
use Tests\TestCase;

class KpiResultTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi_result()
    {
        $data = [
            'score_min' => 85,
            'score_max' => 94,
            'criteria' => 'good',
            'notes' => 'appreciate with reward',
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/results', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_results', $data, 'tenant');
    }

    /** @test */
    public function an_user_can_read_single_kpi_result()
    {
        $kpiResult = factory(KpiResult::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/results/'.$kpiResult->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'score_min' => $kpiResult->score_min,
                'score_max' => $kpiResult->score_max,
                'criteria' => $kpiResult->criteria,
                'notes' => $kpiResult->notes,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_result()
    {
        $kpiCategories = factory(KpiResult::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/results', [], [$this->headers]);

        foreach ($kpiCategories as $kpiResult) {
            $this->assertDatabaseHas('kpi_results', [
                'id' => $kpiResult->id,
                'score_min' => $kpiResult->score_min,
                'score_max' => $kpiResult->score_max,
                'criteria' => $kpiResult->criteria,
                'notes' => $kpiResult->notes,
            ], 'tenant');
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_result()
    {
        $kpiResult = factory(KpiResult::class)->create();

        $data = [
            'id' => $kpiResult->id,
            'score_min' => $kpiResult->score_min,
            'score_max' => $kpiResult->score_max,
            'criteria' => $kpiResult->criteria,
            'notes' => 'propose new challenge',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/results/'.$kpiResult->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_results', $data, 'tenant');

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_result()
    {
        $kpiResult = factory(KpiResult::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/results/'.$kpiResult->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_results', [
            'id' => $kpiResult->id,
        ], 'tenant');
    }
}
