<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use Tests\TestCase;

class MaritalStatusTest extends TestCase
{
    public static $path = '/api/v1/human-resource/employee/marital-statuses';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function getListEmployeeMaritalStatus()
    {
        $response = $this->json('GET', self::$path, [], $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }
}