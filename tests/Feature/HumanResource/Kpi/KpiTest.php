<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KpiTest extends TestCase
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
            'employee_id' => factory(Employee::class)->create()->id,
        ];

        $response = $this->json('POST', 'api/v1/human-resource/kpi/kpis', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi', $data);
    }

    /** @test */
    public function an_user_can_read_single_kpi_category()
    {
        $kpi = factory(Kpi::class)->create();
        $response = $this->json('GET', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $kpi->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_kpi_category()
    {
        $kpiCategories = factory(Kpi::class, 2)->create();

        $response = $this->json('GET', 'api/v1/human-resource/kpi/kpis', [], [$this->headers]);

        foreach ($kpiCategories as $kpi) {
            $this->assertDatabaseHas('kpi', [
                'name' => $kpi->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_kpi_category()
    {
        $kpi = factory(Kpi::class)->create();

        $data = [
            'id' => $kpi->id,
            'person_id' => $kpi->person_id,
            'date' => $kpi->date,
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('kpi', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_kpi_category()
    {
        $kpi = factory(Kpi::class)->create();

        $response = $this->json('DELETE', 'api/v1/human-resource/kpi/kpis/'.$kpi->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('kpi', [
            'id' => $kpi->id,
        ]);
    }
}
