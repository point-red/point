<?php

namespace Tests\Feature\Master;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();

        config()->set('database.default', 'mysql');
    }

    /** @test */
    public function an_user_can_create_user()
    {
        $response = $this->json('POST', 'api/v1/master/users', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
            'password' => 'secret-2016',
        ], [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'John',
            'email' => 'john.doe@gmail.com',
        ], 'mysql');
    }

    /** @test */
    public function an_user_can_read_single_user()
    {
        $response = $this->json('GET', 'api/v1/master/users/'.$this->user->id, [], [$this->headers]);

        $response->assertJson([
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
    public function an_user_can_read_all_user()
    {
        $this->users = factory(User::class, 2)->create();

        $response = $this->json('GET', 'api/v1/master/users', [], [$this->headers]);

        $response->assertStatus(200);
    }

    /* @test */
//    public function an_user_can_update_user()
//    {
//        $data = [
//            'id' => $this->user->id,
//            'name' => 'another name',
//            'email' => 'another@email.com',
//        ];
//
//        $response = $this->json('PUT', 'api/v1/master/users/'.$this->user->id, $data, [$this->headers]);
//
//        $response->assertJson(['data' => $data]);
//
//        $this->assertDatabaseHas('users', $data, 'mysql');
//
//        $response->assertStatus(200);
//    }

    /* @test */
//    public function an_user_can_delete_user()
//    {
//        $response = $this->json('DELETE', 'api/v1/master/users/'.$this->user->id, [], [$this->headers]);
//
//        $response->assertStatus(204);
//
//        $this->assertDatabaseMissing('users', [
//            'name' => $this->user->name,
//            'email' => $this->user->email,
//        ], 'mysql');
//    }
}
