<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $user = factory(User::class)->make();

        Passport::actingAs($user, ['*']);
    }

    /** @test */
    public function can_create_user()
    {
        $response = $this->json('POST', 'api/v1/register', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-password'
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $response->assertStatus(201);
    }
}
