<?php

namespace Tests;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     *  Set up the test.
     */
    public function setUp()
    {
        parent::setUp();

        Artisan::call('config:clear');

        config()->set('database.connections.tenant.driver', 'sqlite');
        config()->set('database.connections.tenant.database', 'database/databaseTenant.sqlite');

        $this->getConnection(DB::getDefaultConnection())->disconnect();

        $this->artisan('tenant:setup-database', [
            'tenant_subdomain' => 'database/databaseTenant.sqlite',
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function signIn()
    {
        $this->user = factory(User::class)->create();

        $this->actingAs($this->user, 'api');
    }
}
