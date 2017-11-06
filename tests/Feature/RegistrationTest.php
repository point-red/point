<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $response = $this->json('POST', 'api/v1/register', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-password',
        ], [$this->headers]);

        $response->assertStatus(201);
    }
}
