<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Database migration can be so slow on local machine
     * Instead run migrate on each test, you can run manually
     * "php artisan migrate:fresh --env=testing"
     * "php artisan migrate:fresh --env=testing --database=tenant --path=database/migrations/tenant"
     * and comment this code below "use RefreshTenantDatabase;"
     * and uncomment "use DatabaseTransactions;"
     *
     * By default we still use "use RefreshTenantDatabase;" for integration with travis, etc
     * So you shouldn't commit this change
     */
    use RefreshTenantDatabase;
    // use DatabaseTransactions;

    /**
     *  Set up the test.
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function tearDown(): void
    {
        $this->logRequestTime();

        parent::tearDown();
    }

    /**
     *
     */
    protected function signIn()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api');
    }

    protected function logRequestTime()
    {
        $start = LARAVEL_START;
        $end = microtime(true);
        $diff = $end - $start;
        Log::channel('testing')->info('['.app('request')->method().'] '
            .app('request')->url()
            .' '
            .$diff);
    }
}
