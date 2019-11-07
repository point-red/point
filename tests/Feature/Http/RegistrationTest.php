<?php

namespace Tests\Feature\Http;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class RegistrationTest extends TestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'mysql');
    }

    /** @test */
    public function user_can_register()
    {
        $response = $this->json('POST', 'api/v1/register', [
            'username' => $this->faker->userName,
            'password' => $this->faker->password(8, 16),
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ], $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => $response->json('data.name'),
            'email' => $response->json('data.email'),
            'first_name' => $response->json('data.first_name'),
            'last_name' => $response->json('data.last_name'),
        ]);
    }
}
