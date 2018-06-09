<?php

namespace Tests\Feature\Master;

use App\Model\HumanResource\Kpi\KpiCategory;
use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiGroupTest extends TestCase
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
            'kpi_category_id' => factory(KpiCategory::class)->create()->id,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/groups', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_groups', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi_group()
    {
        $kpiGroup = factory(KpiGroup::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/groups/'.$kpiGroup->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpiGroup->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_group()
    {
        $kpiGroups = factory(KpiGroup::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/groups', [], [$this->headers]);

        foreach ($kpiGroups as $kpiGroup) {
            $this->assertDatabaseHas('kpi_groups', [
                'name' => $kpiGroup->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_group()
    {
        $kpiGroup = factory(KpiGroup::class)->create();

        $data = [
            'id' => $kpiGroup->id,
            'kpi_category_id' => $kpiGroup->kpi_category_id,
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/groups/'.$kpiGroup->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_groups', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_group()
    {
        $kpiGroup = factory(KpiGroup::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/groups/'.$kpiGroup->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_groups', [
            'id' => $kpiGroup->id,
        ]);
    }
}
