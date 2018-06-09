<?php

namespace Tests\Feature\HumanResource\Kpi;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiCategoryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_kpi_category_name_should_be_unique()
    {
        $kpiCategory = factory(KpiCategory::class)->create();

        $data = [
            'name' => $kpiCategory->name,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/categories', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['name'],
            ],
        ]);

        $response->assertStatus(422);

        $data = [
            'id' => $kpiCategory->id,
            'name' => $kpiCategory->name,
            'date' => $kpiCategory->date,
            'person_id' => $kpiCategory->person_id,
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/categories/'.$kpiCategory->id, $data, [$this->headers]);

        $response->assertJsonMissing([
            'error' => [
                'errors' => ['name'],
            ],
        ]);

        $response->assertStatus(200);
    }
}
