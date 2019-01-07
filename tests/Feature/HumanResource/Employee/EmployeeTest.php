<?php

namespace Tests\Feature\HumanResource\Employee;

use Tests\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_employee_test()
    {
        $data = [
            'name' => 'John Doe'
        ];

        // API Request
        $response = $this->json('POST', 'api/v1/human-resource/employee/employees', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);
    }
}
