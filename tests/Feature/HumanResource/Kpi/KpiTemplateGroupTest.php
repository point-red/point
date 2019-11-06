<?php

namespace Tests\Feature\Master;

use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use Tests\RefreshDatabase;
use Tests\TestCase;

class KpiTemplateGroupTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi_group()
    {
        $data = [
            'name' => 'name',
            'kpi_template_id' => factory(Kpi::class)->create()->id,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/template-groups', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_template_groups', $data, 'tenant');
    }

    /** @test */
    public function an_user_can_read_single_kpi_group()
    {
        $kpiTemplateGroup = factory(KpiTemplateGroup::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/template-groups/'.$kpiTemplateGroup->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpiTemplateGroup->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_group()
    {
        $kpiTemplateGroups = factory(KpiTemplateGroup::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/template-groups', [], [$this->headers]);

        foreach ($kpiTemplateGroups as $kpiTemplateGroup) {
            $this->assertDatabaseHas('kpi_template_groups', [
                'name' => $kpiTemplateGroup->name,
            ], 'tenant');
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_group()
    {
        $kpiTemplateGroup = factory(KpiTemplateGroup::class)->create();

        $data = [
            'id' => $kpiTemplateGroup->id,
            'kpi_template_id' => $kpiTemplateGroup->kpi_template_id,
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/template-groups/'.$kpiTemplateGroup->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_template_groups', $data, 'tenant');

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_group()
    {
        $kpiTemplateGroup = factory(KpiTemplateGroup::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/template-groups/'.$kpiTemplateGroup->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_template_groups', [
            'id' => $kpiTemplateGroup->id,
        ], 'tenant');
    }
}
