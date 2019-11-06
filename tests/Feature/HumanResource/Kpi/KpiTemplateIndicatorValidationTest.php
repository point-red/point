<?php

namespace Tests\Feature\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTemplateIndicatorValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_template_indicator_name_should_be_unique()
    {
        $kpiTemplateIndicator = factory(KpiTemplateIndicator::class)->create();

        $data = [];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/template-indicators', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['name', 'weight', 'target'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiTemplateIndicator->id,
            'name' => 'name',
            'weight' => 20,
            'target' => 5,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/template-indicators/'.$kpiTemplateIndicator->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['name', 'weight', 'target'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
