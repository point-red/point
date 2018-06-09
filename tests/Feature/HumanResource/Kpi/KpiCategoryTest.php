<?php

namespace Tests\Feature\Master;

use App\Model\Master\Person;
use Tests\TestCase;
use App\Model\HumanResource\Kpi\KpiCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_kpi_category()
    {
        $data = [
            'name' => 'name',
            'date' => date('Y-m-d'),
            'person_id' => factory(Person::class)->create()->id,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/categories', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_categories', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi_category()
    {
        $kpiCategory = factory(KpiCategory::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/categories/'.$kpiCategory->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpiCategory->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_category()
    {
        $kpiCategories = factory(KpiCategory::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/categories', [], [$this->headers]);

        foreach ($kpiCategories as $kpiCategory) {
            $this->assertDatabaseHas('kpi_categories', [
                'name' => $kpiCategory->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_category()
    {
        $kpiCategory = factory(KpiCategory::class)->create();

        $data = [
            'id' => $kpiCategory->id,
            'person_id' => $kpiCategory->person_id,
            'date' => $kpiCategory->date,
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/categories/'.$kpiCategory->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi_categories', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_category()
    {
        $kpiCategory = factory(KpiCategory::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/categories/'.$kpiCategory->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi_categories', [
            'id' => $kpiCategory->id,
        ]);
    }
}
