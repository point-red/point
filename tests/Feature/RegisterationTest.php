<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $response = $this->json('POST', 'api/v1/register', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(201);
    }
}
