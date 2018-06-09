<?php

namespace Tests\Feature\Master;

use App\Model\HumanResource\Kpi\KpiCategory;
use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTemplateIndicatorTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi_indicator()
    {
        $data = [
            'name' => 'name',
            'kpi_template_group_id' => factory(KpiCategory::class)->create()->id,
            'weight' => 20,
            'target' => 5,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/template-indicators', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_template_indicators', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi_indicator()
    {
        $kpiTemplateIndicator = factory(KpiTemplateIndicator::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/template-indicators/'.$kpiTemplateIndicator->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpiTemplateIndicator->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_indicator()
    {
        $kpiTemplateIndicators = factory(KpiTemplateIndicator::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/template-indicators', [], [$this->headers]);

        foreach ($kpiTemplateIndicators as $kpiTemplateIndicator) {
            $this->assertDatabaseHas('kpi_template_indicators', [
                'name' => $kpiTemplateIndicator->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_indicator()
    {
        $kpiTemplateIndicator = factory(KpiTemplateIndicator::class)->create();

        $data = [
            'id' => $kpiTemplateIndicator->id,
            'kpi_template_group_id' => $kpiTemplateIndicator->kpi_template_group_id,
            'name' => 'another name',
            'weight' => 20,
            'target' => 5,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/template-indicators/'.$kpiTemplateIndicator->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_template_indicators', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_indicator()
    {
        $kpiTemplateIndicator = factory(KpiTemplateIndicator::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/template-indicators/'.$kpiTemplateIndicator->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_template_indicators', [
            'id' => $kpiTemplateIndicator->id,
        ]);
    }
}
