<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        Passport::actingAs($this->user, ['*']);
    }

    /** @test */
    public function table_and_column_exist()
    {
        $this->assertDatabaseHas('users', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password,
            'remember_token' => $this->user->remember_token,
        ]);

        $this->assertInstanceOf(User::class, $this->user);
    }

    /** @test */
    public function name_should_be_unique()
    {
        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(201);

        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John',
            'email' => 'john.moe@gmail.com',
            'password' => 'secret-password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function email_should_be_unique()
    {
        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'password' => 'secret-password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(201);

        $response = $this->json('POST', 'api/v1/user', [
            'name' => 'John Moe',
            'email' => 'john@gmail.com',
            'password' => 'secret-password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(422);
    }
}
