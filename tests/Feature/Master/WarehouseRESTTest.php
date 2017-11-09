<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\Master\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseRESTTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_warehouse()
    {
        $data = [
            'code' => 'code',
            'name' => 'name',
        ];

        $response = $this->json('POST', 'api/v1/master/warehouses', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('warehouses', $data);
    }

    /** @test */
    public function an_user_can_read_single_warehouse()
    {
        $warehouse = factory(Warehouse::class)->create();
        $response = $this->json('GET', 'api/v1/master/warehouses/'.$warehouse->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'code' => $warehouse->code,
                'name' => $warehouse->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_warehouse()
    {
        $warehouses = factory(Warehouse::class, 2)->create();

        $response = $this->json('GET', 'api/v1/master/warehouses', [], [$this->headers]);

        foreach ($warehouses as $warehouse) {
            $this->assertDatabaseHas('warehouses', [
                'code' => $warehouse->code,
                'name' => $warehouse->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_warehouse()
    {
        $warehouse = factory(Warehouse::class)->create();

        $data = [
            'id' => $warehouse->id,
            'code' => 'another code',
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/master/warehouses/'.$warehouse->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('warehouses', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_warehouse()
    {
        $warehouse = factory(Warehouse::class)->create();

        $response = $this->json('DELETE', 'api/v1/master/warehouses/'.$warehouse->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('warehouses', [
            'code' => $warehouse->code,
            'name' => $warehouse->name,
        ]);
    }
}
