<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTemplateValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_template_name_should_be_unique()
    {
        $kpiTemplate = factory(KpiTemplate::class)->create();

        $data = [
            'name' => '',
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/templates', $data, [$this->headers]);
        info($response->json());
        $response->assertJsonStructure([
            'error' => [
                'errors' => ['name'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiTemplate->id,
            'name' => '',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/templates/'.$kpiTemplate->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['name'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
