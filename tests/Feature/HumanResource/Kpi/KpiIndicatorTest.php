<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiGroup;
use App\Model\HumanResource\Kpi\KpiIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiIndicatorTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi()
    {
        $data = [
            'kpi_group_id' => factory(KpiGroup::class)->create()->id,
            'name' => 'name',
            'weight' => 20,
            'target' => 5,
            'score' => 5,
            'score_percentage' => 5,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/kpi_indicators', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_indicators', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi()
    {
        $kpiIndicator = factory(KpiIndicator::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/kpi_indicators/'.$kpiIndicator->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpiIndicator->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi()
    {
        $kpiIndicators = factory(KpiIndicator::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/kpi_indicators', [], [$this->headers]);

        foreach ($kpiIndicators as $kpiIndicator) {
            $this->assertDatabaseHas('kpi_indicators', [
                'name' => $kpiIndicator->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi()
    {
        $kpiIndicator = factory(KpiIndicator::class)->create();

        $data = [
            'id' => $kpiIndicator->id,
            'kpi_group_id' => $kpiIndicator->kpi_group_id,
            'name' => 'another name',
            'weight' => 20,
            'target' => 5,
            'score' => 5,
            'score_percentage' => 5,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/kpi_indicators/'.$kpiIndicator->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_indicators', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi()
    {
        $kpiIndicator = factory(KpiIndicator::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/kpi_indicators/'.$kpiIndicator->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_indicators', [
            'id' => $kpiIndicator->id,
        ]);
    }
}
