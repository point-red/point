<?php

namespace Tests\Feature\Http\Master;

use Tests\TestCase;

class CustomerTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_customer_test()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        // API Request
        $response = $this->json('POST', '/api/v1/master/customers', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);

        // Check Database
        $this->assertDatabaseHas('customers', [
            'name' => $data['name'],
        ], 'tenant');
    }
}
