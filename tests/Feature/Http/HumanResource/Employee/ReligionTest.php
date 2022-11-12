<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use Tests\TestCase;

class ReligionTest extends TestCase
{
    public static $path = '/api/v1/human-resource/employee/religions';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function getListEmployeeReligion()
    {
        $response = $this->json('GET', self::$path, [], $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }
}