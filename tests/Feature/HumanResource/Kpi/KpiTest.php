<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTest extends TestCase
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
            'indicator' => 'indicator',
            'weight' => 20,
            'target' => 5,
            'score' => 5,
            'score_percentage' => 5,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/kpis', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpis', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi()
    {
        $kpi = factory(Kpi::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'indicator' => $kpi->indicator,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi()
    {
        $kpis = factory(Kpi::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/kpis', [], [$this->headers]);

        foreach ($kpis as $kpi) {
            $this->assertDatabaseHas('kpis', [
                'indicator' => $kpi->indicator,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi()
    {
        $kpi = factory(Kpi::class)->create();

        $data = [
            'id' => $kpi->id,
            'kpi_group_id' => $kpi->kpi_group_id,
            'indicator' => 'another name',
            'weight' => 20,
            'target' => 5,
            'score' => 5,
            'score_percentage' => 5,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpis', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi()
    {
        $kpi = factory(Kpi::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpis', [
            'id' => $kpi->id,
        ]);
    }
}
