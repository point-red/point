<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi_template()
    {
        $data = [
            'name' => 'name',
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/templates', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_templates', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi_template()
    {
        $kpiTemplate = factory(KpiTemplate::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/templates/'.$kpiTemplate->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpiTemplate->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_template()
    {
        $kpiCategories = factory(KpiTemplate::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/templates', [], [$this->headers]);

        foreach ($kpiCategories as $kpiTemplate) {
            $this->assertDatabaseHas('kpi_templates', [
                'name' => $kpiTemplate->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_template()
    {
        $kpiTemplate = factory(KpiTemplate::class)->create();

        $data = [
            'id' => $kpiTemplate->id,
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/templates/'.$kpiTemplate->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_templates', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_template()
    {
        $kpiTemplate = factory(KpiTemplate::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/templates/'.$kpiTemplate->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_templates', [
            'id' => $kpiTemplate->id,
        ]);
    }
}
