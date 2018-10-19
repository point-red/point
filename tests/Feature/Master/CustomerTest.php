<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $data = ['name' => 'Andi Doe'];

        // API Request
        $response = $this->json('POST', 'api/v1/master/customers', $data, [$this->headers]);

        // Check JSON Response
        $response->assertJson([
            'data' => $data,
        ]);

        // Check Status Response
        $response->assertStatus(201);

        // Check Database
        $this->assertDatabaseHas('customers', $data);

        $data = ['name' => 'Andi Doe'];

        // API Request
        $response = $this->json('POST', 'api/v1/master/customers', $data, [$this->headers]);

        // Check JSON Response
        $response->assertJson([
            "code" => 422,
            "message" => "The given data was invalid.",
            "errors" => [
                "name" => [
                    0 => "The name has already been taken."
                ]
            ]
        ]);

        // Check Status Response
        $response->assertStatus(422);

        // Check Database
        $this->assertDatabaseHas('customers', $data);
    }
}
