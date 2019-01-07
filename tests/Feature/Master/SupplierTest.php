<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use Tests\RefreshDatabase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_supplier_test()
    {
        $data = [
            'name' => 'John Doe',
        ];

        // API Request
        $response = $this->json('POST', 'api/v1/master/customers', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);
    }
}
