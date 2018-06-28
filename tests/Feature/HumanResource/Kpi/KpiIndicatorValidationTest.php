<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiIndicatorValidationTest extends TestCase
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
        $kpiIndicator = factory(KpiIndicator::class)->create();

        $data = [];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/kpi_indicators', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['kpi_group_id', 'name', 'weight', 'target', 'score', 'score_percentage'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/kpi_indicators/'.$kpiIndicator->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['kpi_group_id', 'name', 'weight', 'target', 'score', 'score_percentage'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
