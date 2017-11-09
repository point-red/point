<?php

namespace Tests\Feature\Master;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_name_and_email_should_be_unique()
    {
        $user = factory(User::class)->create();

        $response = $this->json('POST', 'api/v1/master/user', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
        ], [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['name', 'email']
            ]
        ]);

        $response->assertStatus(422);
    }
}
