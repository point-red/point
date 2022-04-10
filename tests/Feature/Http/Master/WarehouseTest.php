<?php

namespace Tests\Feature\Http\Master;

use App\Model\Master\Branch;
use App\Model\Master\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    private $url = '/api/v1/master/warehouses';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /**
     * @test
     * @group master/warehouses
     */
    public function create_warehouse()
    {
        /** @var Branch */
        $branch = factory(Branch::class)->create();

        $data = [
            'code' => $this->faker->postcode,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'phone' => $this->faker->e164PhoneNumber,
            'notes' => $this->faker->text,
            'branch_id' => $branch->id,
        ];

        $response = $this->json('POST', $this->url, $data, [$this->headers]);

        $response->assertStatus(201);

        $response->assertJson(['data' => $data]);

        $warehouse = new Warehouse();

        $this->assertDatabaseHas($warehouse->getTable(), $data, $warehouse->getConnectionName());
    }

    /**
     * @test
     * @group master/warehouses
     */
    public function read_single_warehouse()
    {
        /** @var Warehouse */
        $warehouse = factory(Warehouse::class)->create();

        $response = $this->json('GET', $this->url.'/'.$warehouse->id, [], [$this->headers]);

        $response->assertStatus(200);

        $response->assertJson(['data' => $warehouse->toArray()]);
    }

    /**
     * @test
     * @group master/warehouses
     */
    public function read_all_warehouse()
    {
        /** @var Collection<Warehouse> */
        $warehouses = factory(Warehouse::class, 2)->create();

        $response = $this->json('GET', $this->url, [], [$this->headers]);

        $response->assertStatus(200);

        foreach ($warehouses as $warehouse) {
            $response->assertJsonFragment($warehouse->toArray());
        }
    }

    /**
     * @test
     * @group master/warehouses
     */
    public function update_warehouse()
    {
        /** @var Branch */
        $branch = factory(Branch::class)->create();

        /** @var Warehouse */
        $warehouse = factory(Warehouse::class)->create();

        $data = [
            'code' => $this->faker->postcode,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'notes' => $this->faker->text,
            'branch_id' => $branch->id,
        ];

        $response = $this->json('PUT', $this->url.'/'.$warehouse->id, $data, [$this->headers]);

        $response->assertStatus(200);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas($warehouse->getTable(), $data, $warehouse->getConnectionName());
    }

    /**
     * @test
     * @group master/warehouses
     */
    public function delete_warehouse()
    {
        /** @var Warehouse */
        $warehouse = factory(Warehouse::class)->create();

        $response = $this->json('DELETE', $this->url.'/'.$warehouse->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing($warehouse->getTable(), [
            'id' => $warehouse->id,
        ], $warehouse->getConnectionName());
    }
}
