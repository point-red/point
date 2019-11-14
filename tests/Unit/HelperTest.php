<?php

namespace Tests\Unit;

use App\Http\Resources\ApiCollection;
use App\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Model\Master\User as TenantUser;

class HelperTest extends TestCase
{
    /** @test */
    public function get_if_set_test()
    {
        $var = 'test';

        $this->assertIsString(get_if_set($var));

        $this->assertNull(app('request')->var);
    }

    /** @test */
    public function capitalize_test()
    {
        $var = 'test';

        $capitalize = capitalize($var);

        $this->assertStringContainsString('Test', $capitalize);
    }

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
    public function get_invitation_code_test()
    {
        $invitationCode = get_invitation_code();

        $this->assertIsString($invitationCode);
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

    /** @test */
    public function date_tz_test()
    {
        /**
         * UTC = 0
         * Asia/Jakarta = +7
         */

        $date = date_tz('01/10/2019 00:00:00', 'UTC', 'Asia/Jakarta');

        $this->assertStringContainsString('2019-01-10 07:00:00', $date);
    }
}
