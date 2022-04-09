<?php

namespace Tests\Feature\Http\Master;

use App\Model\Master\User;
use App\Model\Project\ProjectUser;
use App\User as UserAccount;
use Tests\TestCase;

class UserTest extends TestCase
{
    private $url = '/api/v1/master/users';

    /**
     * @group master/users
     */
    public function test_create_user()
    {
        /** @var UserAccount */
        $userAccount = factory(UserAccount::class)->create();

        $this->actingAs($userAccount, 'api');

        $response = $this->json('POST', $this->url.'', [
            'name' => $userAccount->name,
            'email' => $userAccount->email,
        ], $this->headers);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'name' => $userAccount->name,
            'email' => $userAccount->email,
            'full_name' => '',
        ]);

        $user = new User();

        $this->assertDatabaseHas($user->getTable(), [
            'name' => $userAccount->name,
            'email' => $userAccount->email,
        ], $user->getConnectionName());
    }

    /**
     * @group master/users
     */
    public function test_get_users()
    {
        /** @var UserAccount */
        $userAccount = factory(UserAccount::class)->create();

        $this->actingAs($userAccount, 'api');

        /** @var User */
        $user = factory(User::class)->create(['id' => $userAccount->id]);

        $response = $this->json('GET', $this->url, [], $this->headers);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'address' => $user->address,
            'phone' => $user->phone,
            'email' => $user->email,
            'full_name' => $user->getFullNameAttribute(),
        ]);
    }

    /**
     * @group master/users
     */
    public function test_get_user_by_id()
    {
        /** @var UserAccount */
        $userAccount = factory(UserAccount::class)->create();

        $this->actingAs($userAccount, 'api');

        /** @var User */
        $user = factory(User::class)->create(['id' => $userAccount->id]);

        $response = $this->json('GET', $this->url.'/'.$user->id, [], $this->headers);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'address' => $user->address,
            'phone' => $user->phone,
            'email' => $user->email,
            'full_name' => $user->getFullNameAttribute(),
        ]);
    }

    /**
     * @group master/users
     */
    public function test_update_user_by_id()
    {
        /** @var UserAccount */
        $userAccount = factory(UserAccount::class)->create();

        $this->actingAs($userAccount, 'api');

        /** @var User */
        $user = factory(User::class)->create(['id' => $userAccount->id]);

        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $fullName = $firstName.' '.$lastName;

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $this->faker->address,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email,
            'full_name' => $fullName,
        ];

        $response = $this->json('PUT', $this->url.'/'.$user->id, $data, $this->headers);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'full_name' => $data['full_name'],
        ]);

        $this->assertDatabaseHas($user->getTable(), [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ], $user->getConnectionName());
    }

    /**
     * @group master/users
     */
    public function test_delete_user_by_id()
    {
        /** @var UserAccount */
        $userAccount = factory(UserAccount::class)->create();

        $this->actingAs($userAccount, 'api');

        /** @var User */
        $user = factory(User::class)->create(['id' => $userAccount->id]);

        $response = $this->json('DELETE', $this->url.'/'.$user->id, [], $this->headers);

        $response->assertStatus(204);

        $this->assertDatabaseMissing($user->getTable(), [
            'id' => $user->id,
        ], $user->getConnectionName());

        $projectUser = new ProjectUser();

        $this->assertDatabaseMissing($projectUser->getTable(), [
            'id' => $user->id,
        ], $projectUser->getConnectionName());
    }
}
