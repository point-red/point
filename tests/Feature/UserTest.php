<?php

namespace Tests\Feature;

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
    public function user_can_create_user()
    {
        $this->json('POST', 'api/v1/user', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-password',
        ], [$this->header])->assertStatus(201);

        $this->assertInstanceOf(User::class, $this->user);
    }

    /** @test */
    public function user_can_read_single_user()
    {
        $this->json('GET', 'api/v1/user/'.$this->user->id, [], [$this->header])->assertJson([
            'data' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'created_at' => $this->user->created_at,
                'updated_at' => $this->user->updated_at,
            ],
        ]);
    }

    /** @test */
    public function user_can_read_all_user()
    {
        $this->user = factory(User::class, 2)->create();

        $this->json('GET', 'api/v1/user', [], [$this->header])->assertStatus(200);
    }

    /** @test */
    public function user_can_update_user()
    {
        $response = $this->json('PUT', 'api/v1/user/'.$this->user->id, [
            'name' => 'another name',
            'email' => 'another@email.com',
        ], [$this->header])->assertJson([
            'data' => [
                'id' => $this->user->id,
                'name' => 'another name',
                'email' => 'another@email.com',
                'created_at' => $this->user->created_at,
                'updated_at' => $this->user->updated_at,
            ],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_delete_user()
    {
        $this->json('DELETE', 'api/v1/user/'.$this->user->id, [], [$this->header])->assertStatus(200);
    }
}
