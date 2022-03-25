<?php

namespace Tests\Feature\Http\Master;

use App\Model\Master\Branch;
use App\Model\Master\Warehouse;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_warehouse()
    {
        $branch = factory(Branch::class)->create();

        $data = [
            'code' => $this->faker->randomNumber(null, false),
            'name' => $this->faker->name,
            'branch_id' => $branch->id,
        ];

        $response = $this->json('POST', '/api/v1/master/warehouses', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('warehouses', $data, 'tenant');
    }

    /** @test */
    public function read_single_warehouse()
    {
        $warehouse = factory(Warehouse::class)->create();

        $response = $this->json('GET', '/api/v1/master/warehouses/'.$warehouse->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'code' => $warehouse->code,
                'name' => $warehouse->name,
            ],
        ]);
    }

    /** @test */
    public function read_all_warehouse()
    {
        $warehouses = factory(Warehouse::class, 2)->create();

        $response = $this->json('GET', '/api/v1/master/warehouses', [], [$this->headers]);

        foreach ($warehouses as $warehouse) {
            $this->assertDatabaseHas('warehouses', [
                'code' => $warehouse->code,
                'name' => $warehouse->name,
            ], 'tenant');
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function update_warehouse()
    {
        $branch = factory(Branch::class)->create();
        $warehouse = factory(Warehouse::class)->create();

        $data = [
            'id' => $warehouse->id,
            'code' => $this->faker->randomNumber(null, false),
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'branch_id' => $branch->id,
        ];

        $response = $this->json('PUT', '/api/v1/master/warehouses/'.$warehouse->id, $data, [$this->headers]);

        $response->assertStatus(200);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('warehouses', $data, 'tenant');
    }

    /** @test */
    public function delete_warehouse()
    {
        $warehouse = factory(Warehouse::class)->create();

        $response = $this->json('DELETE', '/api/v1/master/warehouses/'.$warehouse->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('warehouses', [
            'code' => $warehouse->code,
            'name' => $warehouse->name,
        ], 'tenant');
    }
}
