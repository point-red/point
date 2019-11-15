<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Model\Master\User as TenantUser;

class HelperTest extends TestCase
{
    /** @test */
    public function tenant_test()
    {
        $user = factory(User::class)->create();

        $tenantUser = new TenantUser;
        $tenantUser->id = $user->id;
        $tenantUser->name = $user->name;
        $tenantUser->email = $user->email;
        $tenantUser->save();

        $tenant = tenant($user->id);

        $this->assertIsObject($tenant);

        $this->assertTrue($user->name === $tenant->name);
    }

    /** @test */
    public function pagination_test()
    {
        factory(User::class, 10)->create();

        $users = User::orderBy('id', 'asc');

        $users = pagination($users, 10)->toArray();

        $this->assertArrayHasKey('current_page', $users);
        $this->assertArrayHasKey('last_page', $users);
        $this->assertArrayHasKey('per_page', $users);
        $this->assertArrayHasKey('from', $users);
        $this->assertArrayHasKey('to', $users);
        $this->assertArrayHasKey('path', $users);
        $this->assertArrayHasKey('total', $users);
    }

}
