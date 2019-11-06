<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    // use RefreshTenantDatabase;

    /**
     *  Set up the test.
     */
    public function setUp(): void
    {
        parent::setUp();

        if (! defined('LARAVEL_START')) {
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
