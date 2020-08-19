<?php

namespace Tests\Feature\Http\Master;

use App\Model\Master\Allocation;
use Tests\TestCase;

class AllocationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_allocation()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('POST', '/api/v1/master/allocations', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('allocations', $data, 'tenant');
    }

    /** @test */
    public function read_single_allocation()
    {
        $allocation = factory(Allocation::class)->create();

        $response = $this->json('GET', '/api/v1/master/allocations/'.$allocation->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $allocation->name,
            ],
        ]);
    }

    /** @test */
    public function read_all_allocation()
    {
        $allocations = factory(Allocation::class, 2)->create();

        $response = $this->json('GET', '/api/v1/master/allocations', [], [$this->headers]);

        foreach ($allocations as $allocation) {
            $this->assertDatabaseHas('allocations', [
                'name' => $allocation->name,
            ], 'tenant');
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function update_allocation()
    {
        $allocation = factory(Allocation::class)->create();

        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', '/api/v1/master/allocations/'.$allocation->id, $data, [$this->headers]);

        $response->assertStatus(200);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('allocations', $data, 'tenant');
    }

    /** @test */
    public function delete_allocation()
    {
        $allocation = factory(Allocation::class)->create();

        $response = $this->json('DELETE', '/api/v1/master/allocations/'.$allocation->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('allocations', [
            'code' => $allocation->code,
            'name' => $allocation->name,
        ], 'tenant');
    }
}
