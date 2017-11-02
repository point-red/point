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
    public function table_and_column_exist()
    {
        $user = factory(User::class)->create();

        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'remember_token' => $user->remember_token,
        ]);
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

    /** @test */
    public function name_should_be_unique()
    {
        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-password'
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $response->assertStatus(201);

        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John',
            'email' => 'john.moe@gmail.com',
            'password' => 'secret-password'
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function email_should_be_unique()
    {
        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'password' => 'secret-password'
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $response->assertStatus(201);

        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John Moe',
            'email' => 'john@gmail.com',
            'password' => 'secret-password'
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $response->assertStatus(422);
    }

}
