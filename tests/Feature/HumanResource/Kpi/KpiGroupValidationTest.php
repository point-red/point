<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiGroupValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_group_name_should_be_unique()
    {
        $kpiGroup = factory(KpiGroup::class)->create();

        $data = [
            'kpi_category_id' => $kpiGroup->kpi_category_id,
            'name' => $kpiGroup->name,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/groups', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['name'],
            ],
        ]);

        $response->assertStatus(422);

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/groups/'.$kpiGroup->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['name'],
            ],
        ]);

        $response->assertStatus(200);
    }
}
