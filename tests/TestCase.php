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

        config()->set('database.connections.tenant.driver', env('DB_TENANT_DRIVER'));
        config()->set('database.connections.tenant.database', env('DB_TENANT_DATABASE'));

        $this->getConnection(DB::getDefaultConnection())->disconnect();

        $this->artisan('tenant:seed-fresh-project', [
            'tenant_subdomain' => env('DB_TENANT_DATABASE'),
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
