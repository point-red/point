<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        config()->set('database.default', 'mysql');
    }

    /** @test */
    public function user_can_register()
    {
        $response = $this->json('POST', 'api/v1/register', [
            'username' => 'John Reg',
            'email' => 'john.reg@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'secret-password',
            'last_name' => 'secret-password',
        ], [$this->headers]);

        $response->assertStatus(201);
    }
}
