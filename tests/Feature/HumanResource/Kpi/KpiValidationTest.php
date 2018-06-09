<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_name_should_be_unique()
    {
        $kpi = factory(Kpi::class)->create();

        $data = [];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/kpis', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['kpi_group_id', 'indicator', 'weight', 'target', 'score', 'score_percentage'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['kpi_group_id', 'indicator', 'weight', 'target', 'score', 'score_percentage'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
