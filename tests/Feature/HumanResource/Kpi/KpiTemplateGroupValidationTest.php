<?php

namespace Tests\Feature\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTemplateGroupValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_template_group_name_should_be_unique()
    {
        $kpiTemplateGroup = factory(KpiTemplateGroup::class)->create();

        $data = [
            'name' => 'name'
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/template-groups', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['kpi_template_id'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiTemplateGroup->id,
            'kpi_template_id' => '',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/template-groups/'.$kpiTemplateGroup->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['kpi_template_id'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
